<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Courierapi extends Model
{
    use HasFactory;

    protected $fillable = [
        'type',
        'api_key',
        'client_id',
        'client_secret',
        'username',
        'password',
        'grant_type',
        'secret_key',
        'url',
        'token',
        'refresh_token',
        'token_expires_at',
        'store_id',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
        'token_expires_at' => 'datetime',
    ];
}
