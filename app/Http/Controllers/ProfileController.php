<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ProfileController extends Controller
{
    public function showOwnProfile(Request $request)
    {
        return Inertia::render('Profile', [
            'user' => $request->user(),
            'isOwnProfile' => true
        ]);
    }


    public function show(User $user)
    {
        $isOwnProfile = Auth::check() && Auth::id() === $user->id;

        $profileImage = $user->images()->where('type', 'profile')->latest()->first();
        $coverImage = $user->images()->where('type', 'cover')->latest()->first();

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'company' => $user->company,
            'profile_image' => $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path']) : null,
            'cover_image' => $coverImage ? asset('storage/' . $coverImage->path) : null,
        ];

        return Inertia::render('Profile', [
            'user' => $userData,
            'isOwnProfile' => $isOwnProfile
        ]);
    }



    public function update(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
        ]);

        $request->user()->update($validated);

        return redirect()->back()->with('message', 'Profile updated successfully!');
    }
}
