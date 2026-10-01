<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Childcategory extends Model
{
    use HasFactory;

    protected $casts = [
        'status' => 'boolean',
    ];

    protected $fillable = [
        'childcategoryName',
        'slug',
        'subcategory_id',
        'image',
        'meta_title',
        'meta_description',
        'status',
    ];
    
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id');
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'childcategory_id');
    }
}
