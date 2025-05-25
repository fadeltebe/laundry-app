<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Transaction extends Model
{
    protected $fillable = [
        'laundry_id',
        'branch_id',
        'customer_id',
        'kode',
        'description',
        'received_at',
        'completed_at',
        'status',
        'amount',
        'payment_method',
        'paid_at',
        'payment_status',
    ];
    protected $casts = [
        'received_at' => 'datetime',
        'completed_at' => 'datetime',
        'paid_at' => 'datetime',
    ];
    protected $attributes = [
        'status' => 'Diterima',
        'payment_status' => 'Belum Lunas',
    ];
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }

    public function transactionServices(): HasMany
    {
        return $this->hasMany(TransactionService::class);
    }
}
