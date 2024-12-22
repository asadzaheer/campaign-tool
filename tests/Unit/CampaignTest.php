<?php

namespace Tests\Unit;

use App\Models\Campaign;
use App\Models\User;
use App\Models\Payout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampaignTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $campaign;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create();
        
        $this->campaign = Campaign::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Test Campaign',
            'landing_page_url' => 'https://example.com',
            'activity_status' => 'active'
        ]);
    }

    public function test_can_create_campaign_with_payouts()
    {
        $payoutData = [
            ['country' => 'estonia', 'payout_value' => 1.5],
            ['country' => 'spain', 'payout_value' => 1.2]
        ];

        $campaign = $this->user->campaigns()->create([
            'title' => 'New Campaign',
            'landing_page_url' => 'https://test.com',
            'activity_status' => 'active'
        ]);

        $campaign->payouts()->createMany($payoutData);

        $this->assertDatabaseHas('campaigns', [
            'id' => $campaign->id,
            'title' => 'New Campaign'
        ]);

        foreach ($payoutData as $payout) {
            $this->assertDatabaseHas('payouts', [
                'campaign_id' => $campaign->id,
                'country' => $payout['country'],
                'payout_value' => $payout['payout_value']
            ]);
        }
    }

    public function test_can_update_campaign_with_payouts()
    {
        $this->campaign->payouts()->createMany([
            ['country' => 'estonia', 'payout_value' => 1.0],
            ['country' => 'spain', 'payout_value' => 0.8]
        ]);

        $updatedData = [
            'title' => 'Updated Campaign',
            'payouts' => [
                ['country' => 'bulgaria', 'payout_value' => 2.0],
                ['country' => 'spain', 'payout_value' => 1.5]
            ]
        ];

        $this->campaign->update(['title' => $updatedData['title']]);
        
        $payouts = collect($updatedData['payouts'])->keyBy('country');
        $this->campaign->payouts()->each(function ($payout) use ($payouts) {
            if ($payouts->has($payout->country)) {
                $payout->update([
                    'payout_value' => $payouts->get($payout->country)['payout_value']
                ]);
                $payouts->forget($payout->country);
            } else {
                $payout->delete();
            }
        });

        $this->campaign->payouts()->createMany($payouts->toArray());

        $this->assertDatabaseHas('campaigns', [
            'id' => $this->campaign->id,
            'title' => 'Updated Campaign'
        ]);

        $this->assertDatabaseHas('payouts', [
            'campaign_id' => $this->campaign->id,
            'country' => 'bulgaria',
            'payout_value' => 2.0
        ]);

        $this->assertDatabaseHas('payouts', [
            'campaign_id' => $this->campaign->id,
            'country' => 'spain',
            'payout_value' => 1.5
        ]);

        $this->assertDatabaseMissing('payouts', [
            'campaign_id' => $this->campaign->id,
            'country' => 'estonia'
        ]);
    }

    public function test_campaign_search()
    {
        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Marketing Campaign',
            'activity_status' => 'active',
            'landing_page_url' => 'https://example.com/marketing'
        ]);

        Campaign::factory()->create([
            'user_id' => $this->user->id,
            'title' => 'Sales Campaign',
            'activity_status' => 'paused',
            'landing_page_url' => 'https://example.com/sales'
        ]);

        $searchResults = Campaign::where('title', 'like', '%Marketing%')->get();
        $this->assertEquals(1, $searchResults->count());
        $this->assertEquals('Marketing Campaign', $searchResults->first()->title);

        $activeResults = Campaign::where('activity_status', 'active')->get();
        $this->assertEquals(2, $activeResults->count());
    }

    public function test_campaign_authorization()
    {
        $otherUser = User::factory()->create();
        
        $this->assertTrue($this->user->can('view', $this->campaign));
        $this->assertTrue($this->user->can('update', $this->campaign));
        $this->assertTrue($this->user->can('delete', $this->campaign));
        
        $this->assertFalse($otherUser->can('view', $this->campaign));
        $this->assertFalse($otherUser->can('update', $this->campaign));
        $this->assertFalse($otherUser->can('delete', $this->campaign));
    }

    public function test_can_not_create_campaign_without_atleast_one_payout()
    {
        $campaignData = [
            'title' => 'Campaign Without Payouts',
            'landing_page_url' => 'https://test.com',
            'activity_status' => 'active',
            'payouts' => []
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/campaigns', $campaignData);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['payouts' => 'he payouts field is required.']);
    }
}