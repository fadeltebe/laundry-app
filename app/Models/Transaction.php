<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    protected $fillable = ['laundry_id', 'branch_id', 'description', 'amount'];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }
}
