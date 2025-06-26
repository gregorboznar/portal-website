<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class FriendshipController extends Controller
{
    public function index()
    {
        $currentUser = Auth::user();

        $friendRequests = $currentUser->pendingFriendRequests()
            ->with(['user.images' => function ($query) {
                $query->where('type', 'profile')->latest();
            }])
            ->get()
            ->map(function ($friendship) {
                $profileImage = $friendship->user->images->first();
                return [
                    'id' => $friendship->user->id,
                    'firstname' => $friendship->user->firstname,
                    'lastname' => $friendship->user->lastname,
                    'company' => $friendship->user->company,
                    'slug' => $friendship->user->slug,
                    'profile_image_url' => $profileImage ? asset('storage/' . ($profileImage->optimizations['medium']['path'] ?? $profileImage->path)) : null,
                    'friendship_id' => $friendship->id,
                ];
            });

        $friends = $currentUser->friends()
            ->with(['images' => function ($query) {
                $query->where('type', 'profile')->latest();
            }])
            ->get()
            ->map(function ($friend) {
                $profileImage = $friend->images->first();
                return [
                    'id' => $friend->id,
                    'firstname' => $friend->firstname,
                    'lastname' => $friend->lastname,
                    'company' => $friend->company,
                    'slug' => $friend->slug,
                    'profile_image_url' => $profileImage ? asset('storage/' . ($profileImage->optimizations['medium']['path'] ?? $profileImage->path)) : null,
                ];
            });

        $suggested = User::where('id', '!=', $currentUser->id)
            ->whereNotIn('id', function ($query) use ($currentUser) {
                $query->select('friend_id')
                    ->from('friendships')
                    ->where('user_id', $currentUser->id);
            })
            ->whereNotIn('id', function ($query) use ($currentUser) {
                $query->select('user_id')
                    ->from('friendships')
                    ->where('friend_id', $currentUser->id);
            })
            ->with(['images' => function ($query) {
                $query->where('type', 'profile')->latest();
            }])
            ->limit(10)
            ->get()
            ->map(function ($user) {
                $profileImage = $user->images->first();
                return [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'company' => $user->company,
                    'slug' => $user->slug,
                    'profile_image_url' => $profileImage ? asset('storage/' . ($profileImage->optimizations['medium']['path'] ?? $profileImage->path)) : null,
                ];
            });

        return Inertia::render('Friends', [
            'friendRequests' => $friendRequests,
            'friends' => $friends,
            'suggested' => $suggested,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $friendId = $request->friend_id;

        if ($currentUser->id == $friendId) {
            return response()->json(['message' => 'Cannot send friend request to yourself'], 400);
        }

        $existingFriendship = Friendship::where([
            ['user_id', $currentUser->id],
            ['friend_id', $friendId],
        ])->orWhere([
            ['user_id', $friendId],
            ['friend_id', $currentUser->id],
        ])->first();

        if ($existingFriendship) {
            return response()->json(['message' => 'Friendship already exists'], 400);
        }

        Friendship::create([
            'user_id' => $currentUser->id,
            'friend_id' => $friendId,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Friend request sent successfully']);
    }

    public function accept(Friendship $friendship): JsonResponse
    {
        if ($friendship->friend_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendship->accept();

        return response()->json(['message' => 'Friend request accepted']);
    }

    public function decline(Friendship $friendship): JsonResponse
    {
        if ($friendship->friend_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $friendship->delete();

        return response()->json(['message' => 'Friend request declined']);
    }

    public function destroy(User $user): JsonResponse
    {
        $currentUser = Auth::user();

        Friendship::where([
            ['user_id', $currentUser->id],
            ['friend_id', $user->id],
        ])->orWhere([
            ['user_id', $user->id],
            ['friend_id', $currentUser->id],
        ])->delete();

        return response()->json(['message' => 'Friend removed successfully']);
    }
}
