<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    /** @use HasFactory<\Database\Factories\CampaignFactory> */
    use HasFactory;

    protected $fillable = [
        'title',
        'landing_page_url',
        'activity_status',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function payouts()
    {
        return $this->hasMany(Payout::class);
    }
}
