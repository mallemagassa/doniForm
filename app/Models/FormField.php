<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FormField extends Model
{
    protected $fillable = [
        'program_id',
        'label',
        'field_type',
        'required',
        'options', // JSON field for options in case of select, checkbox, etc.
    ];

    protected $casts = [
        'options' => 'json', // Cast options to JSON
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }
}
