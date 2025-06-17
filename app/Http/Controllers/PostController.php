<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Image;
use App\Models\Comment;
use App\Models\PostView;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;


class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'likes', 'images'])
            ->withCount('likes')
            ->orderByDesc('is_pinned')
            ->orderByDesc('pinned_at')
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'author' => [
                        'name' => $post->user->name,
                        'company' => $post->user->company,
                        'profile_image' => $this->getUserProfileImage($post->user),
                        'slug' => $post->user->slug,
                    ],
                    'content' => $post->content,
                    'type' => $post->type,
                    'poll_options' => $post->poll_options,
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

        return response()->json($posts);
    }

    public function store(Request $request)
    {
        $request->validate([
            'content' => 'required|string|max:5000',
            'type' => 'in:regular,poll',
            'poll_options' => 'array|max:10',
            'poll_options.*' => 'string|max:255',
            'images' => 'array|max:5',
            'images.*' => 'integer',
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->type ?? 'regular',
            'poll_options' => $request->type === 'poll' ? $request->poll_options : null,
        ]);

        if ($request->has('images') && is_array($request->images) && count($request->images) > 0) {
            Image::whereIn('id', $request->images)
                ->update([
                    'imageable_type' => Post::class,
                    'imageable_id' => $post->id,
                ]);
        }

        $post->load(['user', 'images']);

        return response()->json([
            'id' => $post->id,
            'author' => [
                'name' => $post->user->name,
                'company' => $post->user->company,
                'profile_image' => $this->getUserProfileImage($post->user),
                'slug' => $post->user->slug,
            ],
            'content' => $post->content,
            'type' => $post->type,
            'poll_options' => $post->poll_options,
            'timestamp' => $post->created_at->format('j M \a\t g:i A'),
            'likes' => 0,
            'comments' => 0,
            'views' => 0,
            'isLiked' => false,
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
        ]);
    }

    public function toggleLike(Post $post): JsonResponse
    {
        $user = Auth::user();

        if ($user->likedPosts()->where('post_id', $post->id)->exists()) {
            $user->likedPosts()->detach($post->id);
            $post->decrement('likes_count');
            $isLiked = false;
        } else {
            $user->likedPosts()->attach($post->id);
            $post->increment('likes_count');
            $isLiked = true;
        }

        return response()->json([
            'isLiked' => $isLiked,
            'likes' => $post->fresh()->likes_count,
        ]);
    }

    public function show(Post $post)
    {
        $post->increment('views_count');

        $post->load(['user', 'likes', 'images']);

        return response()->json([
            'id' => $post->id,
            'author' => [
                'name' => $post->user->name,
                'company' => $post->user->company,
                'profile_image' => $this->getUserProfileImage($post->user),
                'slug' => $post->user->slug,
            ],
            'content' => $post->content,
            'type' => $post->type,
            'poll_options' => $post->poll_options,
            'timestamp' => $post->created_at->format('j M \a\t g:i A'),
            'likes' => $post->likes_count,
            'comments' => $post->comments_count,
            'views' => $post->views_count,
            'isLiked' => Auth::check() ? $post->isLikedBy(Auth::user()) : false,
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
        ]);
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

    public function getComments(Post $post)
    {
        $comments = $post->comments()
            ->with('user:id,name')
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'created_at' => $comment->created_at->format('j M \a\t g:i A'),
                    'user' => [
                        'id' => $comment->user->id,
                        'name' => $comment->user->name,
                        'profile_image' => $this->getUserProfileImage($comment->user),
                    ],
                ];
            });

        return response()->json([
            'data' => $comments
        ]);
    }

    public function storeComment(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string|max:1000',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'post_id' => $post->id,
            'content' => $request->content,
        ]);

        $post->increment('comments_count');

        $comment->load('user:id,name');

        return response()->json([
            'data' => [
                'id' => $comment->id,
                'content' => $comment->content,
                'created_at' => $comment->created_at->format('j M \a\t g:i A'),
                'user' => [
                    'id' => $comment->user->id,
                    'name' => $comment->user->name,
                    'profile_image' => $this->getUserProfileImage($comment->user),
                ],
            ]
        ]);
    }

    public function trackView(Request $request, Post $post)
    {
        $user = Auth::user();
        $ipAddress = $request->ip();
        $userAgent = $request->userAgent();

        // Check if this user has already viewed this post
        $existingView = PostView::where('post_id', $post->id)
            ->where(function ($query) use ($user, $ipAddress) {
                if ($user) {
                    $query->where('user_id', $user->id);
                } else {
                    $query->where('ip_address', $ipAddress)
                        ->whereNull('user_id');
                }
            })
            ->first();

        if (!$existingView) {
            PostView::create([
                'user_id' => $user?->id,
                'post_id' => $post->id,
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
            ]);

            $post->increment('views_count');
        }

        return response()->json([
            'success' => true,
            'views_count' => $post->fresh()->views_count,
        ]);
    }

    public function togglePin(Post $post): JsonResponse
    {
        $user = Auth::user();



        if ($post->is_pinned) {
            $post->unpin();
            $message = 'Post unpinned successfully';
        } else {
            $post->pin();
            $message = 'Post pinned successfully';
        }
        // // rel the page
        // return redirect()->back();
        // return redirect()->back();
        return response()->json([
            'success' => true,
            'message' => $message,
            'isPinned' => $post->fresh()->is_pinned,
        ]);
    }

    public function softDelete(Post $post): JsonResponse
    {
        $user = Auth::user();

        $post->delete();

        return response()->json([
            'success' => true,
            'message' => 'Post deleted successfully',
        ]);
    }

    private function getUserProfileImage($user): ?string
    {
        $profileImage = $user->images()->where('type', 'profile')->latest()->first();
        return $profileImage ? asset('storage/' . $profileImage->optimizations['medium']['path']) : null;
    }
}
