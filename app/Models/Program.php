<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Program extends Model
{
    protected $fillable = [
        'title',
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

    public function evaluationCriteria(): HasMany
    {
        return $this->hasMany(EvaluationCriteria::class);
    }


    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class);
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