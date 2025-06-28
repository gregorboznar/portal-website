<?php

namespace App\Models;

use App\Events\MessageSent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Support\Str;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'conversation_id',
        'user_id',
        'content',
        'type',
        'metadata',
        'read_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'read_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Message $message) {
            if (empty($message->uuid)) {
                $message->uuid = Str::uuid();
            }
        });

        static::created(function (Message $message) {
            $message->conversation->update([
                'last_message_at' => $message->created_at,
            ]);

            $message->load(['conversation', 'user']);
            broadcast(new MessageSent($message));
        });
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getRouteKeyName(): string
    {
        return 'uuid';
    }
}
