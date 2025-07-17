<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'uuid',
        'firstname',
        'lastname',
        'slug',
        'email',
        'email_verified_at',
        'password',
        'company',
        'position',
        'about',
        'social_media',
        'total_tickets',
        'remaining_tickets',
        'role',
        'displayed_badges',
        'registered_at',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
            if (empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model);
            }
        });

        static::updating(function ($model) {
            if ($model->isDirty(['firstname', 'lastname']) && empty($model->slug)) {
                $model->slug = static::generateUniqueSlug($model);
            }
        });
    }

    protected static function generateUniqueSlug($user)
    {
        $name = $user->firstname && $user->lastname
            ? "{$user->firstname} {$user->lastname}"
            : 'user';

        $baseSlug = Str::slug($name);
        $slug = $baseSlug;
        $counter = 1;

        while (static::where('slug', $slug)->where('id', '!=', $user->id ?? 0)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'registered_at' => 'date',
            'password' => 'hashed',
            'social_media' => 'array',
            'displayed_badges' => 'array',
        ];
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function likedPosts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'post_likes');
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function profileImage()
    {
        return $this->images()->where('type', 'profile')->latest()->first();
    }

    public function coverImage()
    {
        return $this->images()->where('type', 'cover')->latest()->first();
    }

    public function getProfileImageUrl(): ?string
    {
        $profileImage = $this->profileImage();
        return $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path']) : null;
    }

    public function sentFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'user_id');
    }

    public function receivedFriendRequests(): HasMany
    {
        return $this->hasMany(Friendship::class, 'friend_id');
    }

    public function friends(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'friendships', 'user_id', 'friend_id')
            ->wherePivot('status', 'accepted')
            ->withPivot(['status', 'accepted_at']);
    }

    public function pendingFriendRequests()
    {
        return $this->receivedFriendRequests()
            ->where('status', 'pending')
            ->with('user');
    }

    public function isFriendWith(User $user): bool
    {
        return $this->friends()->where('friend_id', $user->id)->exists();
    }

    public function hasSentFriendRequestTo(User $user): bool
    {
        return $this->sentFriendRequests()
            ->where('friend_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function hasReceivedFriendRequestFrom(User $user): bool
    {
        return $this->receivedFriendRequests()
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function getFriendshipStatus(User $user): ?string
    {
        if ($this->isFriendWith($user)) {
            return 'friends';
        }

        if ($this->hasSentFriendRequestTo($user)) {
            return 'request_sent';
        }

        if ($this->hasReceivedFriendRequestFrom($user)) {
            return 'request_received';
        }

        return null;
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->firstname . ' ' . $this->lastname) ?: 'Unknown User';
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class, 'conversation_participants')
            ->using(ConversationParticipant::class)
            ->withPivot(['joined_at', 'last_read_at'])
            ->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
