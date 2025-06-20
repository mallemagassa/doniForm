<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Application extends Model
{
    protected $fillable = [
        'user_id',
        'program_id',
        'status',
        'submitted_at',
        'notes',
        'form_data',
    ];

    protected $casts = [
        'form_data' => 'json',
        'submitted_at' => 'datetime',
    ];

    public function program()
    {
        return $this->belongsTo(Program::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function evaluations()
    {
        return $this->hasMany(Evaluation::class);
    }

    public function documents()
    {
        return $this->hasMany(Document::class);
    }
}
