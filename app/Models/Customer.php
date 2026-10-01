<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Customer extends Authenticatable
{
    use HasFactory;

    protected $guard = 'customer';
    protected $fillable = [
        'name',
        'slug',
        'phone',
        'email',
        'password',
        'verify',
        'status',
        'district',
        'area',
        'address',
        'image',
        'forgot',
        'balance',
    ];
    protected $hidden = [
      'password', 'remember_token',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'status' => 'integer',
        'verify' => 'boolean',
    ];

    /**
     * Mutator to safely convert status string ('active'/'inactive') or int to database tinyint (1/0).
     */
    public function setStatusAttribute(mixed $value): void
    {
        if (is_string($value)) {
            $this->attributes['status'] = in_array(strtolower($value), ['active', '1', 'true'], true) ? 1 : 0;
        } else {
            $this->attributes['status'] = (int) $value;
        }
    }

    public function cust_area()
    {
        return $this->belongsTo(District::class,'area');
    }
    public function orders()
    {
        return $this->hasMany(Order::class,'customer_id');
    }
        public function reviews()
    {
        return $this->hasMany(Review::class, 'customer_id');
    }
}
