<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Document extends Model
{
    protected $fillable = [
        'application_id',
        'label',
        'file_path',
    ];

      public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
