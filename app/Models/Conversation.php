<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Conversation extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'name',
        'type',
        'metadata',
        'last_message_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_message_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Conversation $conversation) {
            if (empty($conversation->uuid)) {
                $conversation->uuid = Str::uuid();
            }
        });
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function participants(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'conversation_participants')
            ->using(ConversationParticipant::class)
            ->withPivot(['joined_at', 'last_read_at'])
            ->withTimestamps();
    }

    public function lastMessage(): HasMany
    {
        return $this->messages()->latest();
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
