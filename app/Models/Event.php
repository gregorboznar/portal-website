<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'short_description',
        'date',
        'time',
        'end_date',
        'location',
        'url',
        'registration_url',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
        'end_date' => 'date',
        'time' => 'datetime:H:i',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($event) {
            if (empty($event->uuid)) {
                $event->uuid = (string) Str::uuid();
            }
        });
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('date', '>=', now()->format('Y-m-d'));
    }

    public function scopePast($query)
    {
        return $query->where('date', '<', now()->format('Y-m-d'));
    }
}
