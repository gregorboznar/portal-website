<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class PostController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'likes'])
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
        ]);

        $post = Post::create([
            'user_id' => Auth::id(),
            'content' => $request->content,
            'type' => $request->type ?? 'regular',
            'poll_options' => $request->type === 'poll' ? $request->poll_options : null,
        ]);

        $post->load('user');

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

        $post->load(['user', 'likes']);

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
        ]);
    }
}
