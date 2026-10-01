<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OutboxMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_name',
        'payload',
        'status',
        'retry_count',
        'error_message',
        'processed_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'retry_count' => 'integer',
        'processed_at' => 'datetime',
    ];
}