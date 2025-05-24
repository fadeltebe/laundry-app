<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'laundry_id',
        'name',
        'phone',
        'address',
    ];

    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }
    public function branches()
    {
        return $this->hasMany(Branch::class);
    }
}
