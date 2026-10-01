<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IdempotencyKey extends Model
{
    use HasFactory;

    protected $fillable = [
        'idempotency_key',
        'request_path',
        'request_hash',
        'response_body',
        'status_code',
        'expires_at',
    ];

    protected $casts = [
        'status_code' => 'integer',
        'expires_at' => 'datetime',
    ];
}