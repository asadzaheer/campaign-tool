<?php

namespace App\Http\Controllers;

use App\Http\Requests\CampaignRequest;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $campaigns = $request->user()->campaigns();

        if ($request->has('search')) {
            $campaigns->where(function ($query) use ($request) {
                if (!empty($request->query('search'))) {
                    $query->where('title', 'like', "%{$request->query('search')}%");
                }

                if (!empty($request->query('status'))) {
                    $query->where('activity_status', $request->query('status'));
                }
            });
        }

        return ['campaigns' => $campaigns->with('payouts')->paginate()];
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CampaignRequest $request)
    {
        $fields = $request->validated();

        $payouts = collect($fields['payouts']);
        unset($fields['payouts']);

        $campaign = $request->user()->campaigns()->create($fields);

        $campaign->payouts()->createMany($payouts);

        return ['campaign' => $campaign->load('payouts')];
    }

    /**
     * Display the specified resource.
     */
    public function show(Campaign $campaign)
    {
        $this->authorize('view', $campaign);

        return ['campaign' => $campaign->load('payouts')];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CampaignRequest $request, Campaign $campaign)
    {
        $this->authorize('update', $campaign);

        $fields = $request->validated();

        if (isset($fields['payouts'])) {
            $payouts = collect($fields['payouts'])->keyBy('country');
            unset($fields['payouts']);
        }

        $campaign->update($fields);

        if (isset($payouts)) {
            $campaign->payouts()->each(function ($payout) use ($payouts) {
                if ($payouts->has($payout->country)) {
                    $payout->update([
                        'payout_value' => $payouts->get($payout->country)['payout_value'],
                    ]);
                    $payouts->forget($payout->country);
                } else {
                    $payout->delete();
                }
            });

            $campaign->payouts()->createMany($payouts->toArray());
        }

        return ['campaign' => $campaign->load('payouts')];
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Campaign $campaign)
    {
        $this->authorize('delete', $campaign);

        $campaign->delete();

        return ['message' => 'Campaign deleted successfully'];
    }
}
