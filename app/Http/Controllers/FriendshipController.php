<?php

namespace App\Http\Controllers;

use App\Models\Friendship;
use App\Models\User;
use Illuminate\Http\Request;
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

    public function store(Request $request)
    {
        $request->validate([
            'friend_id' => 'required|exists:users,id',
        ]);

        $currentUser = Auth::user();
        $friend = User::findOrFail($request->friend_id);

        if ($currentUser->id == $friend->id) {
            return redirect()->back()->withErrors(['error' => 'Cannot send friend request to yourself']);
        }

        if (
            $currentUser->isFriendWith($friend) ||
            $currentUser->hasSentFriendRequestTo($friend) ||
            $currentUser->hasReceivedFriendRequestFrom($friend)
        ) {
            return redirect()->back()->withErrors(['error' => 'Friendship relationship already exists']);
        }

        Friendship::create([
            'user_id' => $currentUser->id,
            'friend_id' => $friend->id,
            'status' => 'pending',
        ]);

        return redirect()->back()->with('success', 'Friend request sent successfully');
    }

    public function accept(Friendship $friendship)
    {
        if ($friendship->friend_id !== Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized']);
        }

        $friendship->accept();

        return redirect()->back()->with('success', 'Friend request accepted');
    }

    public function decline(Friendship $friendship)
    {
        if ($friendship->friend_id !== Auth::id()) {
            return redirect()->back()->withErrors(['error' => 'Unauthorized']);
        }

        $friendship->delete();

        return redirect()->back()->with('success', 'Friend request declined');
    }

    public function destroy(User $user)
    {
        $currentUser = Auth::user();

        Friendship::where([
            ['user_id', $currentUser->id],
            ['friend_id', $user->id],
        ])->orWhere([
            ['user_id', $user->id],
            ['friend_id', $currentUser->id],
        ])->delete();

        return redirect()->back()->with('success', 'Friend removed successfully');
    }
}
