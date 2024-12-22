<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Campaign;
use App\Models\Payout;
use Illuminate\Support\Facades\Hash;

class UserCampaignSeeder extends Seeder
{
    private array $countries = ['estonia', 'spain', 'bulgaria'];

    public function run(): void
    {
        // Create first user with 20 campaigns
        $user1 = User::factory()->create([
            'name' => 'User One',
            'email' => 'user1@example.com',
            'password' => Hash::make('password123'),
        ]);

        for ($i = 0; $i < 20; $i++) {
            $campaign = Campaign::factory()->create([
                'user_id' => $user1->id,
                'title' => "Campaign {$i} - User 1",
                'landing_page_url' => "https://example.com/campaign-{$i}",
                'activity_status' => $i % 2 === 0 ? 'active' : 'paused',
            ]);

            // Random number of payouts (1-3) for each campaign
            $numberOfPayouts = rand(1, 3);
            $campaignCountries = $this->countries;
            shuffle($campaignCountries);
            $selectedCountries = array_slice($campaignCountries, 0, $numberOfPayouts);
            
            foreach ($selectedCountries as $country) {
                Payout::factory()->create([
                    'campaign_id' => $campaign->id,
                    'country' => $country,
                    'payout_value' => rand(5000, 20000) / 100, // Random value between 50.00 and 200.00
                ]);
            }
        }

        // Create second user with 15 campaigns
        $user2 = User::factory()->create([
            'name' => 'User Two',
            'email' => 'user2@example.com',
            'password' => Hash::make('password123'),
        ]);

        for ($i = 0; $i < 15; $i++) {
            $campaign = Campaign::factory()->create([
                'user_id' => $user2->id,
                'title' => "Campaign {$i} - User 2",
                'landing_page_url' => "https://example.com/user2/campaign-{$i}",
                'activity_status' => $i % 2 === 0 ? 'active' : 'paused',
            ]);

            // Random number of payouts (1-3) for each campaign
            $numberOfPayouts = rand(1, 3);
            $campaignCountries = $this->countries;
            shuffle($campaignCountries);
            $selectedCountries = array_slice($campaignCountries, 0, $numberOfPayouts);
            
            foreach ($selectedCountries as $country) {
                Payout::factory()->create([
                    'campaign_id' => $campaign->id,
                    'country' => $country,
                    'payout_value' => rand(5000, 20000) / 100, // Random value between 50.00 and 200.00
                ]);
            }
        }

        // Create third user with no campaigns
        User::factory()->create([
            'name' => 'User Three',
            'email' => 'user3@example.com',
            'password' => Hash::make('password123'),
        ]);
    }
}
