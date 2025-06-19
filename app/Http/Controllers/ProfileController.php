<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProfileController extends Controller
{
    protected ImageUploadService $imageUploadService;

    public function __construct(ImageUploadService $imageUploadService)
    {
        $this->imageUploadService = $imageUploadService;
    }

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
            'uuid' => $user->uuid,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'company' => $user->company,
            'position' => $user->position,
            'about' => $user->about,
            'social_media' => $user->social_media,
            'total_tickets' => $user->total_tickets,
            'remaining_tickets' => $user->remaining_tickets,
            'role' => $user->role,
            'displayed_badges' => $user->displayed_badges,
            'profile_image_url' => $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path']) : null,
            'cover_image' => $coverImage ? asset('storage/' . $coverImage->path) : null,
            'profile_image' => $profileImage ? asset('storage/' . $profileImage->path) : null,
            'slug' => $user->slug,
            'created_at' => $user->created_at,
        ];


        $posts = Post::where('user_id', $user->id)
            ->with(['user', 'likes', 'images', 'pollVotes'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->get()
            ->map(function ($post) {
                $pollResults = $post->type === 'poll' ? $post->getPollResults() : null;
                $userVote = Auth::check() && $post->type === 'poll' ? $post->getUserVote(Auth::user()) : null;

                return [
                    'id' => $post->id,
                    'author' => [
                        'firstname' => $post->user->firstname,
                        'lastname' => $post->user->lastname,
                        'company' => $post->user->company,
                        'profile_image' => $this->getUserProfileImage($post->user),
                        'slug' => $post->user->slug,
                    ],
                    'content' => $post->content,
                    'type' => $post->type,
                    'poll_options' => $post->poll_options,
                    'poll_results' => $pollResults,
                    'user_vote' => $userVote,
                    'has_voted' => Auth::check() && $post->type === 'poll' ? $post->hasVotedBy(Auth::user()) : false,
                    'timestamp' => $post->created_at->format('j M \a\t g:i A'),
                    'likes' => $post->likes_count,
                    'comments' => $post->comments_count,
                    'views' => $post->views_count,
                    'isLiked' => Auth::check() ? $post->isLikedBy(Auth::user()) : false,
                    'isPinned' => $post->is_pinned,
                    'canManage' => Auth::check() && Auth::id() === $post->user_id,
                    'images' => $post->images->map(function ($image) {
                        return [
                            'id' => $image->id,
                            'url' => asset('storage/' . $image->path),
                            'filename' => $image->filename,
                            'original_filename' => $image->original_filename,
                            'width' => $image->width,
                            'height' => $image->height,
                            'optimizations' => $this->formatOptimizations($image->optimizations),
                        ];
                    }),
                ];
            });

        return Inertia::render('Profile', [
            'user' => $userData,
            'posts' => $posts,
            'isOwnProfile' => $isOwnProfile
        ]);
    }

    public function edit(User $user)
    {
        $currentUser = Auth::user();
        $hasPermission = $currentUser->role === 'admin' || $currentUser->role === 'god' || $currentUser->id === $user->id;
        $hasGodPermission = $currentUser->role === 'god';

        if (!$hasPermission) {
            abort(403, 'Unauthorized');
        }

        $profileImage = $user->images()->where('type', 'profile')->latest()->first();

        $userData = [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'email' => $user->email,
            'company' => $user->company,
            'position' => $user->position,
            'about' => $user->about,
            'social_media' => $user->social_media,
            'total_tickets' => $user->total_tickets,
            'remaining_tickets' => $user->remaining_tickets,
            'role' => $user->role,
            'displayed_badges' => $user->displayed_badges ?? [],
            'profile_image_url' => $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path']) : null,
            'slug' => $user->slug,
        ];

        return Inertia::render('EditProfile', [
            'user' => $userData,
            'badges' => [], // Add badges logic here if needed
            'hasPermission' => $hasPermission,
            'hasGodPermission' => $hasGodPermission,
            'libraryUrl' => asset(''),
        ]);
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        // Handle UUID parameter from route
        if ($request->route('uuid')) {
            $uuid = $request->route('uuid');
            $targetUser = User::where('uuid', $uuid)->firstOrFail();
        } else {
            $targetUser = $user;
        }

        $hasPermission = $user->role === 'admin' || $user->role === 'god' || $user->id === $targetUser->id;

        if (!$hasPermission) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $rules = [
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'firstname' => 'nullable|string|max:255',
            'lastname' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'string',
                'email',
                'max:255',
                Rule::unique('users')->ignore($targetUser->id)
            ],
            'company' => 'nullable|string|max:255',
            'position' => 'nullable|string|max:255',
            'about' => 'nullable|string|max:1000',
            'linkedin' => 'nullable|string|max:255',
            'facebook' => 'nullable|string|max:255',
            'instagram' => 'nullable|string|max:255',
            'total_tickets' => 'nullable|integer|min:0',
            'remaining_tickets' => 'nullable|integer|min:0',
            'role' => Rule::in(['user', 'admin', 'god']),
            'password' => 'nullable|string|min:6',
            'password_confirmation' => 'nullable|string|same:password',
            'profile_image' => 'nullable|image|max:3072', // 3MB max
            'displayed_badges' => 'nullable|json',
        ];

        // Only allow admin/god to change certain fields
        if ($user->role !== 'admin' && $user->role !== 'god') {
            unset($rules['total_tickets'], $rules['remaining_tickets'], $rules['role']);
            if ($user->id !== $targetUser->id) {
                unset($rules['email']);
            }
        }

        $validated = $request->validate($rules);

        // Handle social media data
        $socialMedia = [];
        if ($request->has('linkedin')) {
            $socialMedia['linkedin'] = $request->linkedin;
        }
        if ($request->has('facebook')) {
            $socialMedia['facebook'] = $request->facebook;
        }
        if ($request->has('instagram')) {
            $socialMedia['instagram'] = $request->instagram;
        }

        if (!empty($socialMedia)) {
            $validated['social_media'] = $socialMedia;
        }

        // Handle password update
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Remove password confirmation from validated data
        unset($validated['password_confirmation']);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            try {
                // Delete old profile image
                $targetUser->images()->where('type', 'profile')->delete();

                // Upload new profile image
                $image = $this->imageUploadService->uploadForModel(
                    $request->file('profile_image'),
                    $targetUser
                );

                // Set the image type to profile
                $image->update(['type' => 'profile']);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Image upload failed: ' . $e->getMessage()]);
            }
        }

        // Handle displayed badges
        if ($request->has('displayed_badges')) {
            $displayedBadges = json_decode($request->displayed_badges, true);
            $validated['displayed_badges'] = $displayedBadges;
        }

        // Update user
        $targetUser->update($validated);

        return redirect()->back()->with('success', 'Profile updated successfully');
    }

    public function destroy(Request $request)
    {
        $currentUser = Auth::user();


        if ($request->route('uuid')) {
            $uuid = $request->route('uuid');
            $user = User::where('uuid', $uuid)->firstOrFail();
        } else {
            return response()->json(['success' => false, 'message' => 'UUID parameter required'], 400);
        }

        $hasPermission = $currentUser->role === 'admin' || $currentUser->role === 'god';

        if (!$hasPermission) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        try {

            $user->images()->delete();


            $user->delete();

            return response()->json(['success' => true, 'message' => 'User deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Failed to delete user: ' . $e->getMessage()]);
        }
    }

    public function toggleDisplayedBadge(Request $request)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'badge_id' => 'required|integer',
            'show' => 'required|boolean'
        ]);

        $displayedBadges = $user->displayed_badges ?? [];

        if ($request->show) {
            if (!in_array($request->badge_id, $displayedBadges)) {
                $displayedBadges[] = $request->badge_id;
            }
        } else {
            $displayedBadges = array_values(array_filter($displayedBadges, fn($id) => $id !== $request->badge_id));
        }

        $user->update(['displayed_badges' => $displayedBadges]);

        return response()->json(['success' => true]);
    }

    private function getUserProfileImage($user): ?string
    {
        $profileImage = $user->images()->where('type', 'profile')->latest()->first();
        return $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path']) : null;
    }

    private function formatOptimizations(?array $optimizations): array
    {
        if (!$optimizations) {
            return [];
        }

        $formatted = [];
        foreach ($optimizations as $key => $optimization) {
            if (isset($optimization['path'])) {
                $formatted[$key] = [
                    'url' => asset('storage/' . $optimization['path']),
                    'width' => $optimization['width'] ?? null,
                    'height' => $optimization['height'] ?? null,
                ];
            }
        }

        return $formatted;
    }
}
