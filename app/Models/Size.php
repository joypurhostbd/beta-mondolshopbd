<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Size extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $fillable = [
        'sizeName',
        'status',
    ];

    /**
     * Get the product size pivot entries.
     */
    public function productSizes(): HasMany
    {
        return $this->hasMany(Productsize::class, 'size_id');
    }

    /**
     * Get all products associated with this size.
     */
    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'productsizes', 'size_id', 'product_id');
    }
}
