<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Contact extends Model
{
    use HasFactory;
    protected $table = 'contacts';

    protected $fillable = [
        'hotline',
        'hotmail',
        'phone',
        'email',
        'address',
        'maplink',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
