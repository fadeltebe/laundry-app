<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AddOn extends Model
{
    protected $fillable = ['name', 'unit', 'initial_stock'];

    public function totalUsed(): int
    {
        return $this->transactionServiceAddOns()->sum('quantity_used');
    }

    public function remainingStock(): int
    {
        return $this->initial_stock - $this->totalUsed();
    }

    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }
}
