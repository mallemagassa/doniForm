<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationCriteria extends Model
{
    protected $fillable = [
        'program_id',
        'label',
        'description',
    ];
    
    
    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }
    
    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function evaluationCriteriaItems(): HasMany
    {
        return $this->hasMany(EvaluationCriteriaItem::class);
    }
}
