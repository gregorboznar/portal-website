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

    public function scopeWithLikesCount($query)
    {
        return $query->withCount('likes');
    }
}
