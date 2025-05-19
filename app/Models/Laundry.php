<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Laundry extends Model
{
    protected $fillable = [
        'name',
        'slug',
    ];

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
