<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ChatController extends Controller
{
    public function index(): Response
    {
        $user = Auth::user();
        $conversations = $user->conversations()
            ->with(['participants.images', 'lastMessage.user'])
            ->orderByDesc('last_message_at')
            ->get()
            ->map(function ($conversation) use ($user) {
                $otherParticipants = $conversation->participants->where('id', '!=', $user->id);
                $lastMessage = $conversation->lastMessage->first();

                return [
                    'id' => $conversation->id,
                    'uuid' => $conversation->uuid,
                    'name' => $conversation->name ?: $otherParticipants->pluck('full_name')->join(', '),
                    'type' => $conversation->type,
                    'participants' => $otherParticipants->map(function ($participant) {
                        return [
                            'id' => $participant->id,
                            'name' => $participant->full_name,
                            'slug' => $participant->slug,
                            'avatar' => $participant->getProfileImageUrl(),
                        ];
                    }),
                    'last_message' => $lastMessage ? [
                        'content' => $lastMessage->content,
                        'user_name' => $lastMessage->user->full_name,
                        'created_at' => $lastMessage->created_at,
                        'is_mine' => $lastMessage->user_id === $user->id,
                    ] : null,
                    'last_message_at' => $conversation->last_message_at,
                ];
            });

        $friends = $user->friends()->with('images')->get()->map(function ($friend) {
            return [
                'id' => $friend->id,
                'name' => $friend->full_name,
                'slug' => $friend->slug,
                'avatar' => $friend->getProfileImageUrl(),
            ];
        });

        $users = User::with('images')->get()->map(function ($user) {
            return [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'slug' => $user->slug,
                'avatar' => $user->getProfileImageUrl(),
            ];
        });

        return Inertia::render('Chat', [
            'conversations' => $conversations,
            'friends' => $friends,
            'users' => $users,
        ]);
    }

    public function show(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        $conversation->load('participants.images');

        if (!$conversation->participants->contains($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $messages = $conversation->messages()
            ->with('user.images')
            ->orderBy('created_at')
            ->get()
            ->map(function ($message) use ($user) {
                return [
                    'id' => $message->id,
                    'uuid' => $message->uuid,
                    'content' => $message->content,
                    'type' => $message->type,
                    'user' => [
                        'id' => $message->user->id,
                        'name' => $message->user->full_name,
                        'slug' => $message->user->slug,
                        'avatar' => $message->user->getProfileImageUrl(),
                    ],
                    'is_mine' => $message->user_id === $user->id,
                    'created_at' => $message->created_at,
                ];
            });

        return response()->json([
            'conversation' => [
                'id' => $conversation->id,
                'uuid' => $conversation->uuid,
                'name' => $conversation->name,
                'type' => $conversation->type,
                'participants' => $conversation->participants->map(function ($participant) {
                    return [
                        'id' => $participant->id,
                        'name' => $participant->full_name,
                        'slug' => $participant->slug,
                        'avatar' => $participant->getProfileImageUrl(),
                    ];
                }),
            ],
            'messages' => $messages,
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'participants' => 'required|array|size:1',
            'participants.*' => 'exists:users,id',
        ]);

        $user = Auth::user();
        $otherUserId = $request->participants[0];

        if ($otherUserId == $user->id) {
            return response()->json(['error' => 'Cannot start conversation with yourself'], 400);
        }

        $existingConversation = Conversation::where('type', 'direct')
            ->whereHas('participants', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->whereHas('participants', function ($query) use ($otherUserId) {
                $query->where('user_id', $otherUserId);
            })
            ->withCount('participants')
            ->having('participants_count', '=', 2)
            ->first();

        if ($existingConversation) {
            return response()->json([
                'conversation' => [
                    'uuid' => $existingConversation->uuid,
                ]
            ]);
        }

        $conversation = Conversation::create([
            'name' => null,
            'type' => 'direct',
        ]);

        $conversation->participants()->attach([
            $user->id => ['joined_at' => now()],
            $otherUserId => ['joined_at' => now()],
        ]);

        return response()->json([
            'conversation' => [
                'uuid' => $conversation->uuid,
            ]
        ], 201);
    }

    public function sendMessage(Request $request, Conversation $conversation): JsonResponse
    {
        $request->validate([
            'content' => 'required|string|max:1000',
            'type' => 'in:text,image,file',
        ]);

        $user = Auth::user();

        if (!$conversation->participants->contains($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $message = Message::create([
            'conversation_id' => $conversation->id,
            'user_id' => $user->id,
            'content' => $request->content,
            'type' => $request->type ?? 'text',
        ]);

        $message->load('user.images');

        return response()->json([
            'message' => [
                'id' => $message->id,
                'uuid' => $message->uuid,
                'content' => $message->content,
                'type' => $message->type,
                'user' => [
                    'id' => $message->user->id,
                    'name' => $message->user->full_name,
                    'slug' => $message->user->slug,
                    'avatar' => $message->user->getProfileImageUrl(),
                ],
                'created_at' => $message->created_at,
            ]
        ], 201);
    }

    public function markAsRead(Conversation $conversation): JsonResponse
    {
        $user = Auth::user();

        if (!$conversation->participants->contains($user)) {
            abort(403, 'You are not a participant in this conversation.');
        }

        $conversation->participants()->updateExistingPivot($user->id, [
            'last_read_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }
}
