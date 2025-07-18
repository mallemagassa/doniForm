<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NoteItem extends Model
{
    protected $fillable = [
        'note',
        'grille_item_id'
    ];

    public function grilleItem(){
        return $this->belongsTo(GrilleItem::class);
    }
}
