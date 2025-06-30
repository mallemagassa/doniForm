<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation_criteria_items extends Model
{
    protected $fillable = [
        'title',
        'description',
        'evaluation_criteria_id',
    ];

    public function evaluationCriteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class);
    }

}
