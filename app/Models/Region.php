<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Region extends Model
{
    protected $fillable = [
        'name',
        'description',
        'country',
        'status',
    ];

    public function programs(): HasMany
    {
        return $this->hasMany(Program::class);
    }
}
