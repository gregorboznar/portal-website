<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'likes', 'images'])
            ->withCount('likes')
            ->latest()
            ->get()
            ->map(function ($post) {
                return [
                    'id' => $post->id,
                    'author' => [
                        'name' => $post->user->name,
                        'company' => $post->user->company,
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
            'images.*' => 'integer|exists:images,id',
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->type ?? 'regular',
            'poll_options' => $request->type === 'poll' ? $request->poll_options : null,
        ]);

        if ($request->has('images') && is_array($request->images)) {
            Image::whereIn('id', $request->images)->update([
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
        foreach ($optimizations as $size => $data) {
            $formatted[$size] = [
                'url' => asset('storage/' . $data['path']),
                'width' => $data['width'],
                'height' => $data['height'],
            ];
        }

        return $formatted;
    }
}
