<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'customer_id',
        'payment_method',
        'amount',
        'payment_status',
        'trx_id',
        'sender_number',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'order_id' => 'integer',
        'customer_id' => 'integer',
    ];
}
