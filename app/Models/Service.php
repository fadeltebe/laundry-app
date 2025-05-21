<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $fillable = ['nama_layanan', 'satuan', 'harga'];



    public function laundry()
    {
        return $this->belongsTo(Laundry::class);
    }
}
