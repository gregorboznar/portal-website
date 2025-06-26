<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class MemberDirectoryController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = Auth::user();
        $search = $request->input('search');

        $query = User::where('id', '!=', $currentUser->id)
            ->whereNull('deleted_at')
            ->with(['images' => function ($query) {
                $query->where('type', 'profile')->latest();
            }]);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('firstname', 'like', "%{$search}%")
                    ->orWhere('lastname', 'like', "%{$search}%")
                    ->orWhere('company', 'like', "%{$search}%");
            });
        }

        $members = $query->orderBy('firstname')
            ->orderBy('lastname')
            ->get()
            ->map(function ($user) use ($currentUser) {
                $profileImage = $user->images->first();

                $friendshipStatus = null;
                if ($currentUser->isFriendWith($user)) {
                    $friendshipStatus = 'friends';
                } elseif ($currentUser->hasSentFriendRequestTo($user)) {
                    $friendshipStatus = 'request_sent';
                } elseif ($currentUser->hasReceivedFriendRequestFrom($user)) {
                    $friendshipStatus = 'request_received';
                }

                return [
                    'id' => $user->id,
                    'firstname' => $user->firstname,
                    'lastname' => $user->lastname,
                    'company' => $user->company,
                    'slug' => $user->slug,
                    'profile_image_url' => $profileImage ? asset('storage/' . ($profileImage->optimizations['medium']['path'] ?? $profileImage->path)) : null,
                    'friendship_status' => $friendshipStatus,
                ];
            });

        return Inertia::render('MemberDirectory', [
            'members' => $members,
            'search' => $search,
        ]);
    }
}
