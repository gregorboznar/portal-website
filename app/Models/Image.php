<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Image extends Model
{
    protected $fillable = [
        'uuid',
        'filename',
        'original_filename',
        'path',
        'mime_type',
        'size',
        'width',
        'height',
        'optimizations',
        'alt_text',
        'imageable_type',
        'imageable_id',
        'type',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    protected $casts = [
        'optimizations' => 'array',
    ];

    public function imageable()
    {
        return $this->morphTo();
    }
}
