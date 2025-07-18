<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrilleItem extends Model
{
    //
    protected $fillable = [
        'titre',
        'base_notation',
        'grille_label_id',
    ];

    protected $with = ['noteItems'];

    public function grilleLabel(): BelongsTo{
        return $this->belongsTo(GrilleLabel::class);
    }

    public function noteItems(): HasMany{
        return $this->hasMany(NoteItem::class);
    }
}
