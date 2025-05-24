<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TransactionService extends Model
{

    protected $fillable = [
        // 'laundry_id',
        'transaction_id',
        'service_id',
        'add_ons_id',
        'price',
        'weight',
        'subtotal',
    ];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(Transaction::class);
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class);
    }
}
