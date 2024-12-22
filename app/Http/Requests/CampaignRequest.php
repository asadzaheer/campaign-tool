<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CampaignRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        $rules = [
            'title' => ['string', 'max:255'],
            'landing_page_url' => ['string', 'url'],
            'activity_status' => ['in:active,paused'],
            'payouts' => ['array', 'min:1'],
            'payouts.*.country' => ['string', 'in:estonia,spain,bulgaria'],
            'payouts.*.payout_value' => ['numeric', 'min:0'],
        ];

        if ($this->isMethod('POST')) {
            foreach ($rules as $field => $ruleset) {
                $rules[$field] = ['required', ...$ruleset];
            }
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'payouts.*.country.required' => 'The country is required.',
            'payouts.*.country.string' => 'The country must be a string.',
            'payouts.*.country.in' => 'The country is invalid. Only estonia, spain, and bulgaria are allowed.',
            'payouts.*.payout_value.required' => 'The payout value is required.',
            'payouts.*.payout_value.numeric' => 'The payout value must be a number.',
            'payouts.*.payout_value.min' => 'The payout value must be at least 0.',
        ];
    }
}