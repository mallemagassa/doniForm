<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    protected $fillable = [
        'form_program_id',
        'label',
        'field_type',
        'required',
        'options', 
    ];

    protected $casts = [
        'options' => 'json', // Cast options to JSON
    ];

    public function formProgram(): BelongsTo
    {
        return $this->belongsTo(FormProgram::class);
    }
}
