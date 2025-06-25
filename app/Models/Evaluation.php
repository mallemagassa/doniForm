<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Evaluation extends Model
{
    protected $fillable = [
        'user_id',
        'application_id',
        'score',
        'comment',
        'criterion_id',
    ];

    protected $casts = [
        'score' => 'integer',
        'comment' => 'string',
    ];

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function criterion(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class);
    }
}
