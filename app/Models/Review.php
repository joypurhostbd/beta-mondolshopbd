<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Review extends Model
{
    use HasFactory;

    protected $casts = [
        'ratting' => 'integer',
        'rating' => 'integer',
        'status' => 'string',
        'product_id' => 'integer',
        'customer_id' => 'integer',
    ];

    protected $fillable = [
        'product_id',
        'customer_id',
        'name',
        'email',
        'ratting',
        'rating',
        'review',
        'status',
    ];

    /**
     * Resolve the actual rating column in the reviews table.
     */
    public static function getRatingColumn(): string
    {
        static $column = null;
        if ($column === null) {
            $column = Schema::hasColumn('reviews', 'rating') ? 'rating' : 'ratting';
        }
        return $column;
    }

    /**
     * Calculate average rating safely across database schemas.
     */
    public static function averageRating(): float
    {
        return round((float) (static::avg(static::getRatingColumn()) ?? 0), 1);
    }

    public function getRatingAttribute()
    {
        return $this->attributes['rating'] ?? $this->attributes['ratting'] ?? null;
    }

    public function getRattingAttribute()
    {
        return $this->getRatingAttribute();
    }

    public function setRatingAttribute($value)
    {
        $target = static::getRatingColumn();
        $this->attributes[$target] = (int) $value;

        if ($target === 'rating' && array_key_exists('ratting', $this->attributes)) {
            unset($this->attributes['ratting']);
        } elseif ($target === 'ratting' && array_key_exists('rating', $this->attributes)) {
            unset($this->attributes['rating']);
        }
    }

    public function setRattingAttribute($value)
    {
        $this->setRatingAttribute($value);
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }
}
