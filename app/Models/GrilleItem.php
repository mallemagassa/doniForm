<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrilleItem extends Model
{
    //
    protected $fillable = [
        'titre',
        'note_1',
        'note_2',
        'note_3',
        'grille_label_id',
    ];

    public function grilleLabel(): BelongsTo{
        return $this->belongsTo(GrilleLabel::class);
    }
}
