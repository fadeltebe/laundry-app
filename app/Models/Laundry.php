<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function getTenantKeyName(): string
    {
        return 'id';
    }

    public function getTenantKey(): mixed
    {
        return $this->getKey();
    }

    public function getTenantName(): string
    {
        return $this->nama;
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
