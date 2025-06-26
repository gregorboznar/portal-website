<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Friendship extends Model
{
    protected $fillable = [
        'user_id',
        'friend_id',
        'status',
        'accepted_at',
        'blocked_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'blocked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function friend(): BelongsTo
    {
        return $this->belongsTo(User::class, 'friend_id');
    }

    public function accept(): void
    {
        $this->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        $reciprocal = self::where('user_id', $this->friend_id)
            ->where('friend_id', $this->user_id)
            ->first();

        if (!$reciprocal) {
            self::create([
                'user_id' => $this->friend_id,
                'friend_id' => $this->user_id,
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
        } else {
            $reciprocal->update([
                'status' => 'accepted',
                'accepted_at' => now(),
            ]);
        }
    }

    public function block(): void
    {
        $this->update([
            'status' => 'blocked',
            'blocked_at' => now(),
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeBlocked($query)
    {
        return $query->where('status', 'blocked');
    }
}
