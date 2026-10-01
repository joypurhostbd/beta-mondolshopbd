<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignReview extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'name',
        'description',
        'image',
        'status',
    ];

    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
}
