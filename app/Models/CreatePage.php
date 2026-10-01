<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CreatePage extends Model
{
    use HasFactory;

    protected $table = 'create_pages';

    protected $fillable = [
        'name',
        'title',
        'slug',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'integer',
    ];
}
