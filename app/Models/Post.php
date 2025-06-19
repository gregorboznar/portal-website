<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Post extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'content',
        'type',
        'poll_options',
        'poll_answers',
        'likes_count',
        'comments_count',
        'views_count',
        'is_pinned',
        'pinned_at',
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
        'poll_options' => 'array',
        'poll_answers' => 'array',
        'is_pinned' => 'boolean',
        'pinned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function likes(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'post_likes');
    }

    public function isLikedBy(User $user): bool
    {
        return $this->likes()->where('user_id', $user->id)->exists();
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function postViews(): HasMany
    {
        return $this->hasMany(PostView::class);
    }

    public function pollVotes(): HasMany
    {
        return $this->hasMany(PollVote::class);
    }

    public function hasVotedBy(User $user): bool
    {
        return $this->pollVotes()->where('user_id', $user->id)->exists();
    }

    public function getUserVote(User $user): ?int
    {
        $vote = $this->pollVotes()->where('user_id', $user->id)->first();
        return $vote ? $vote->option_index : null;
    }

    public function getPollResults(): array
    {
        if ($this->type !== 'poll' || !$this->poll_options) {
            return [];
        }

        $totalVotes = $this->pollVotes()->count();
        $results = [];

        foreach ($this->poll_options as $index => $option) {
            $votes = $this->pollVotes()->where('option_index', $index)->count();
            $percentage = $totalVotes > 0 ? round(($votes / $totalVotes) * 100, 1) : 0;

            $results[] = [
                'option' => $option,
                'votes' => $votes,
                'percentage' => $percentage,
            ];
        }

        return $results;
    }

    public function scopeWithLikesCount($query)
    {
        return $query->withCount('likes');
    }

    public function pin(): void
    {
        $this->update([
            'is_pinned' => true,
            'pinned_at' => now(),
        ]);
    }

    public function unpin(): void
    {
        $this->update([
            'is_pinned' => false,
            'pinned_at' => null,
        ]);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeNotPinned($query)
    {
        return $query->where('is_pinned', false);
    }
}
