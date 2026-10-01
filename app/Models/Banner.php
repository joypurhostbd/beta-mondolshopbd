<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;
    protected $casts = ['status' => 'boolean'];
    protected $fillable = [
        'link',
        'category_id',
        'image',
        'status',
    ];
    public function category()
    {
        return $this->belongsTo(BannerCategory::class, 'category_id')->select('id', 'name');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }
}
