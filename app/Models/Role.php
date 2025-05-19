<?php

namespace App\Models;

use App\Models\Laundry;
use Spatie\Permission\Models\Role as SpatieRole;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends SpatieRole
{
    public function laundry(): BelongsTo
    {
        return $this->belongsTo(Laundry::class);
    }
}
