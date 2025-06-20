<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Program extends Model
{
    protected $fillable = [
        'title',
        'description',
        'region',
        'start_date',
        'end_date',
        'form_structure',
        'status',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function applications()
    {
        return $this->hasMany(Application::class);
    }

    public function evaluationCriteria()
    {
        return $this->hasMany(EvaluationCriteria::class);
    }


    public function formFields()
    {
        return $this->hasMany(FormField::class);
    }

    protected $casts = [
        'form_structure' => 'json',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

}
