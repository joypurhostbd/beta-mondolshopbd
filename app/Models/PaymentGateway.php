<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;
    protected $fillable = [
        'type',
        'app_key',
        'app_secret',
        'username',
        'password',
        'base_url',
        'success_url',
        'return_url',
        'prefix',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
