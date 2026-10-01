<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http ;
class IncompleteOrder extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id',
        'name',
        'phone',
        'address',
        'data',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'product_price' => 'decimal:2',
        'product_id' => 'integer',
    ];

    protected $table = "incomplete_orders" ;
  
}
