<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EvaluationCriteriaItem extends Model
{
    protected $fillable = [
        'title',
        'description',
        'evaluation_criteria_id',
        'is_checked',
    ];

    public function evaluationCriteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class);
    }

}
