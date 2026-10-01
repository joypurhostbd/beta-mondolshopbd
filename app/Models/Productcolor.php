<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productcolor extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'color_id',
        'color',
    ];
    public function color(){
        return $this->belongsTo(Color::class, 'color_id');
    }
}
