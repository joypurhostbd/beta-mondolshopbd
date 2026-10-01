<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productimage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'image',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];
}
