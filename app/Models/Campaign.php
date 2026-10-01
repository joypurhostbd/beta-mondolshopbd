<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'offer_title',
        'slug',
        'image_one',
        'image_two',
        'image_three',
        'product_id',
        'video_url',
        'start_date',
        'end_date',
        'special_price',
        'free_shipping',
        'description',
        'short_description',
        'banner',
        'review',
        'status',
        'meta_title',
        'meta_description',
        'meta_keywords',
    ];

    protected $casts = [
        'status' => 'integer',
        'free_shipping' => 'boolean',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'special_price' => 'decimal:2',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id')->select('id', 'name', 'slug', 'old_price', 'new_price', 'stock', 'product_code');
    }

    public function images()
    {
        return $this->hasMany(CampaignReview::class, 'campaign_id')->select('id', 'image', 'campaign_id');
    }

    public function reviews()
    {
        return $this->hasMany(CampaignReview::class, 'campaign_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeRunning($query)
    {
        return $query->where('status', 1)
            ->where(function ($q) {
                $q->whereNull('end_date')
                    ->orWhere('end_date', '>=', now());
            });
    }

    public function getYoutubeIdAttribute(): ?string
    {
        if (empty($this->video_url)) {
            return null;
        }

        $url = $this->video_url;
        if (preg_match('/(?:youtu\.be\/|youtube\.com\/(?:embed\/|v\/|watch\?v=|watch\?.+&v=))([\w-]{11})/', $url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    public function getYoutubeEmbedUrlAttribute(): ?string
    {
        $id = $this->youtube_id;
        return $id ? "https://www.youtube.com/embed/{$id}" : null;
    }

    public function getIsExpiredAttribute(): bool
    {
        if (!$this->end_date) {
            return false;
        }

        return now()->gt($this->end_date);
    }
}
