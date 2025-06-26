<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Image;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserController extends Controller
{
  public function index()
  {
    $currentUser = Auth::user();
    $hasAdminAccess = $currentUser && in_array($currentUser->role, ['admin', 'god']);

    if (!$hasAdminAccess) {
      abort(403, 'Unauthorized');
    }

    $users = User::select([
      'id',
      'uuid',
      'firstname',
      'lastname',
      'email',
      'company',
      'position',
      'role',
      'total_tickets',
      'remaining_tickets',
      'created_at',
      'slug'
    ])
      ->whereNull('deleted_at')
      ->with(['images' => function ($query) {
        $query->where('type', 'profile')->latest();
      }])
      ->orderBy('created_at', 'desc')
      ->get()
      ->map(function ($user) {
        $profileImage = $user->images->first();

        return [
          'id' => $user->id,
          'uuid' => $user->uuid,
          'firstname' => $user->firstname,
          'lastname' => $user->lastname,
          'email' => $user->email,
          'company' => $user->company,
          'position' => $user->position,
          'role' => $user->role,
          'total_tickets' => $user->total_tickets,
          'remaining_tickets' => $user->remaining_tickets,
          'created_at' => $user->created_at,
          'slug' => $user->slug,
          'profile_image_url' => $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path'] ?? $profileImage->path) : null,
        ];
      });

    return Inertia::render('users/Index', [
      'users' => $users,
      'canManageUsers' => $hasAdminAccess
    ]);
  }

  public function create()
  {
    return Inertia::render('users/Create');
  }

  public function store(Request $request)
  {
    $validated = $request->validate([
      'firstname' => 'required|string|max:255',
      'lastname' => 'required|string|max:255',
      'email' => 'required|email|unique:users,email',
      'password' => 'required|string|min:8|confirmed',
      'role' => 'required|string|in:admin,user,god',
      'company' => 'nullable|string|max:255',
      'linkedin' => 'nullable|string|max:255',
      'facebook' => 'nullable|string|max:255',
      'email_verified_at' => 'nullable|date',
      'profile_image' => 'nullable|integer|exists:images,id',
    ]);

    $socialMedia = [];
    if ($validated['linkedin']) {
      $socialMedia['linkedin'] = $validated['linkedin'];
    }
    if ($validated['facebook']) {
      $socialMedia['facebook'] = $validated['facebook'];
    }

    $userData = [
      'firstname' => $validated['firstname'],
      'lastname' => $validated['lastname'],
      'email' => $validated['email'],
      'password' => bcrypt($validated['password']),
      'role' => $validated['role'],
      'company' => $validated['company'],
      'social_media' => !empty($socialMedia) ? $socialMedia : null,
      'email_verified_at' => $validated['email_verified_at'] ? Carbon::parse($validated['email_verified_at']) : now(),
    ];

    $user = User::create($userData);

    if ($validated['profile_image']) {
      $image = Image::find($validated['profile_image']);
      if ($image) {
        $image->update([
          'imageable_type' => User::class,
          'imageable_id' => $user->id,
        ]);
      }
    }

    return redirect()->route('users')->with('success', 'User created successfully');
  }
}
