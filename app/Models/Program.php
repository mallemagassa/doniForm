<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Program extends Model
{
    protected $fillable = [
        'title',
        'sigle',
        'description',
        'region_id',
        'start_date',
        'end_date',
        'status',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function grilleLabels(): HasMany
    {
        return $this->hasMany(GrilleLabel::class);
    }

    public function evaluationCriteria(): HasOne
    {
        return $this->hasOne(EvaluationCriteria::class);
    }


    public function formProgram(): HasOne
    {
        return $this->hasOne(FormProgram::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    // protected $casts = [
    //     'start_date' => 'date',
    //     'end_date' => 'date',
    // ];

}