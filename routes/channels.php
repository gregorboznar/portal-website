<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return $user && (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{uuid}', function ($user, $uuid) {
    $conversation = Conversation::where('uuid', $uuid)->first();

    if (!$conversation) {
        return false;
    }

    return $conversation->participants()->where('user_id', $user->id)->exists();
});
Broadcast::channel('presence', function ($user) {
    return [
        'id' => $user->id,
        'name' => $user->full_name,
        'slug' => $user->slug,
        'avatar' => $user->profileImage()?->url,
    ];
});
