<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

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
      'name',
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
          'name' => $user->name ?: ($user->firstname && $user->lastname ? "{$user->firstname} {$user->lastname}" : ''),
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

    return Inertia::render('Users', [
      'users' => $users,
      'canManageUsers' => $hasAdminAccess
    ]);
  }
}
