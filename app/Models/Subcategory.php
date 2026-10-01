<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subcategory extends Model
{
    use HasFactory;
    protected $casts = ['status' => 'boolean'];
    protected $fillable = [
        'subcategoryName',
        'slug',
        'category_id',
        'image',
        'meta_title',
        'meta_description',
        'status',
    ];
    public function childcategories() {
        return $this->hasMany(Childcategory::class, 'subcategory_id')->where('status', 1);
    }
    public function category() {
        return $this->belongsTo(Category::class, 'category_id');
    }
    
    public function menuchildcategories()
    {
        return $this->hasMany(Childcategory::class, 'subcategory_id')->select('id','slug','subcategory_id','childcategoryName')->where('status',1);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'subcategory_id');
    }
    
    
}
