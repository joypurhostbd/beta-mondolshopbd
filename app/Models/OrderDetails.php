<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'product_name',
        'purchase_price',
        'sale_price',
        'qty',
        'product_color',
        'product_size',
        'product_image',
    ];

    protected $casts = [
        'sale_price' => 'decimal:2',
        'purchase_price' => 'decimal:2',
        'qty' => 'integer',
        'order_id' => 'integer',
        'product_id' => 'integer',
    ];

    public function image()
    {
        return $this->belongsTo(Productimage::class, 'product_id', 'product_id')->select('id', 'product_id', 'image');
    }

    public function featuredImageRelation()
    {
        return $this->belongsTo(Productimage::class, 'product_id', 'product_id')
            ->where('is_featured', true)
            ->select('id', 'product_id', 'image', 'is_featured');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function shipping()
    {
        return $this->belongsTo(Shipping::class, 'order_id', 'order_id')->select('id', 'order_id', 'name', 'phone', 'address');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id')->select('id', 'invoice_id', 'order_status', 'user_id', 'amount', 'created_at');
    }
}
