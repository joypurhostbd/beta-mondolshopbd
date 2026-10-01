<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'subcategory_id',
        'childcategory_id',
        'brand_id',
        'product_code',
        'purchase_price',
        'old_price',
        'new_price',
        'stock',
        'description',
        'short_description',
        'pro_unit',
        'product_color',
        'product_size',
        'status',
        'topsale',
        'feature_product',
        'meta_title',
        'meta_keyword',
        'meta_description',
        'campaign_id',
    ];

    protected $casts = [
        'new_price' => 'decimal:2',
        'old_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'stock' => 'integer',
        'status' => 'integer',
        'category_id' => 'integer',
        'campaign_id' => 'integer',
    ];
    public function getRouteKeyName() {
        return 'slug';
    }
    public function campaign()
    {
        return $this->belongsTo(Campaign::class, 'campaign_id');
    }
    public function campaigns()
    {
        return $this->hasMany(Campaign::class, 'product_id');
    }
    public function image()
    {
        return $this->hasOne(Productimage::class, 'product_id')->select('id','image','product_id');
    }
    public function featuredImage()
    {
        return $this->hasOne(Productimage::class, 'product_id')
            ->where('is_featured', true)
            ->select('id', 'image', 'product_id', 'is_featured');
    }
    public function images()
    {
        return $this->hasMany(Productimage::class, 'product_id')->select('id','image','product_id','is_featured');
    }
    public function reviews()
    {
        return $this->hasMany(Review::class, 'product_id')->select('id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id')->select('id','name','slug');
    }
    public function subcategory()
    {
        return $this->belongsTo(Subcategory::class, 'subcategory_id')->select('id','subcategoryName','slug');
    }
    public function childcategory()
    {
        return $this->belongsTo(Childcategory::class, 'childcategory_id')->select('id','childcategoryName','slug');
    }
    public function brand()
    {
        return $this->belongsTo(Brand::class, 'brand_id')->select('id','name','slug');
    }
    public function sizes()
    {
        return $this->belongsToMany('App\Models\Size','productsizes')->withTimestamps();
    }
    public function colors()
    {
        return $this->belongsToMany('App\Models\Color','productcolors')->withTimestamps();
    }
    
    public function prosizes()
    {
        return $this->hasMany('App\Models\Productsize');
    }
    public function procolors()
    {
        return $this->hasMany('App\Models\Productcolor');
    }
    
     public function prosize()
    {
        return $this->hasOne(Productsize::class, 'product_id');
    }
     public function procolor()
    {
        return $this->hasOne(Productcolor::class, 'product_id');
    }
}
