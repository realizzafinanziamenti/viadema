<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Attachment extends Model
{
    protected $fillable = [
        'file_name',
        'file_path',
        'mime_type',
        'file_size',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected function casts(): array
    {
        return [
            'file_size' => 'integer',
        ];
    }

    /**
     * Get the parent attachable model
     */
    public function attachable(): MorphTo
    {
        return $this->morphTo();
    }
}
