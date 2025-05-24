<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransactionService extends Model
{

    protected $fillable = [
        'laundry_id',
        'transaction_id',
        'service_id',
        'add_ons_id',
        'price',
        'weight',
        'subtotal',
    ];

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function addOn()
    {
        return $this->belongsTo(AddOn::class, 'add_ons_id');
    }
}
