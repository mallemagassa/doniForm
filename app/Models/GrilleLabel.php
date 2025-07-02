<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrilleLabel extends Model
{
    protected $fillable = [
        'nom',
        'program_id'
    ];

    public function grilleItems(): HasMany{
        return $this->hasMany(GrilleItem::class);
    }

    public function program(): BelongsTo{
        return $this->belongsTo(Program::class);
    }


}
