<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EcomPixel extends Model
{
    use HasFactory;
    protected $table = 'ecom_pixels';

    protected $fillable = [
        'code',
        'access_token',
        'test_event_code',
        'status',
        'capi_status',
    ];

    protected $casts = [
        'status' => 'integer',
        'capi_status' => 'integer',
    ];
}

