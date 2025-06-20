<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function criterion()
    {
        return $this->belongsTo(EvaluationCriteria::class);
    }
}
