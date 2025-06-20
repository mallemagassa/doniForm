<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    protected $fillable = [
        'application_id',
        'label',
        'file_path',
    ];

    protected $casts = [
        'file_size' => 'integer',
    ];

    public function application()
    {
        return $this->belongsTo(Application::class);
    }
}
