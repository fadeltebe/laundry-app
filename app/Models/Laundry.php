<?php

namespace App\Models;

use App\Models\Service;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Laundry extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'nama_owner',
        'kontak_owner',
        'logo',
        'slogan',
        'alamat',
        'kontak',
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

    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
    public function addOns(): HasMany
    {
        return $this->hasMany(AddOn::class);
    }
    public function transactionServices(): HasMany
    {
        return $this->hasMany(TransactionService::class);
    }
}
