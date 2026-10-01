<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Productsize extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'size_id',
        'size',
    ];
    public function size(){
        return $this->belongsTo(Size::class, 'size_id');
    }
}
