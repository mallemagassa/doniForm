<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EvaluationCriteria extends Model
{
    protected $fillable = [
        'program_id',
        'label',
        'description',
        'max_score',
        'weight',
    ];

    protected $casts = [
        'weight' => 'float', 
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }
}
