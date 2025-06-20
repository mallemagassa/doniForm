<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ApplicationLog extends Model
{
    protected $fillable = [
        'application_id',
        'user_id',
        'action_type',
        'comment',
    ];

    protected $casts = [
        'action' => 'string',
        'description' => 'string',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
