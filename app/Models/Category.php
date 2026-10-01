<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $casts = [
        'status' => 'boolean',
        'front_view' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'slug',
        'image',
        'front_view',
        'meta_title',
        'meta_description',
        'status',
        'parent_id',
    ];
    
    public function getRouteKeyName() {
        return 'slug';
    }
    public function categories()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function subcategories() {
        return $this->hasMany(Subcategory::class, 'category_id')->where('status', 1);
    }
    
    public function menusubcategories() {
        return $this->hasMany(Subcategory::class, 'category_id')->select('id','slug','subcategoryName','category_id')->where('status', 1);
    }

    public function childrenCategories()
    {
        return $this->hasMany(Category::class, 'parent_id')->with('categories');
    }
    
    public function menuchildcategories()
    {
        return $this->hasMany(Childcategory::class, 'subcategory_id')->select('id','slug','subcategory_id','childcategoryName')->where('status',1);
    }
    
    
    public function homeproducts()
    {
        return $this->hasMany(Product::class, 'category_id');
    }
    public function menuproducts()
    {
        return $this->hasMany(Product::class, 'category_id')->limit(8);
    }

    public function products()
    {
        return $this->hasMany(Product::class, 'category_id')->select('id', 'name', 'slug', 'category_id', 'new_price', 'old_price')->orderBy('id','DESC');
    }
    
    


}
