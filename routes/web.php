<?php

use App\Http\Controllers\ChatController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\FriendshipController;
use App\Http\Controllers\MemberDirectoryController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

// Broadcasting routes for WebSocket authentication
Broadcast::routes(['middleware' => ['web', 'auth']]);

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('profile/{user:slug}', [ProfileController::class, 'show'])->middleware(['auth', 'verified'])->name('profile');
Route::get('edit-profile/{user:slug}', [ProfileController::class, 'edit'])->middleware(['auth', 'verified'])->name('edit-profile');

Route::get('friends', [FriendshipController::class, 'index'])->middleware(['auth', 'verified'])->name('friends');
Route::get('member-directory', [MemberDirectoryController::class, 'index'])->middleware(['auth', 'verified'])->name('member-directory');
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('events', [EventController::class, 'index'])->name('events');
    Route::get('events/create', [EventController::class, 'create'])->name('events.create');
    Route::post('events', [EventController::class, 'store'])->name('events.store');
    Route::get('events/{event}', [EventController::class, 'show'])->name('events.show');
    Route::get('events/{event}/edit', [EventController::class, 'edit'])->name('events.edit');
    Route::put('events/{event}', [EventController::class, 'update'])->name('events.update');
    Route::delete('events/{event}', [EventController::class, 'destroy'])->name('events.destroy');
});

Route::get('whitney-wire-report', function () {
    return Inertia::render('WhitneyWireReport');
})->middleware(['auth', 'verified'])->name('whitney-wire-report');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/posts', [PostController::class, 'index']);
    Route::post('/api/posts', [PostController::class, 'store']);
    Route::post('/api/posts/{post}/like', [PostController::class, 'toggleLike']);
    Route::post('/api/posts/{post}/pin', [PostController::class, 'togglePin']);
    Route::post('/api/posts/{post}/vote', [PostController::class, 'votePoll']);
    Route::delete('/api/posts/{post}', [PostController::class, 'softDelete']);
    Route::get('/api/posts/{post}', [PostController::class, 'show']);
    Route::get('/api/posts/{post}/comments', [PostController::class, 'getComments']);
    Route::post('/api/posts/{post}/comments', [PostController::class, 'storeComment']);
    Route::post('/api/posts/{post}/view', [PostController::class, 'trackView']);
    Route::post('/api/images/upload', [ImageController::class, 'upload']);
    Route::post('/api/images/upload-profile', [ImageController::class, 'uploadProfile']);
    Route::post('/api/images/upload-cover', [ImageController::class, 'uploadCover']);
    Route::delete('/api/images/delete-profile', [ImageController::class, 'deleteProfile']);
    Route::delete('/api/images/delete-cover', [ImageController::class, 'deleteCover']);

    // Friendship routes
    Route::post('/friendships', [FriendshipController::class, 'store']);
    Route::patch('/friendships/{friendship}/accept', [FriendshipController::class, 'accept']);
    Route::delete('/friendships/{friendship}/decline', [FriendshipController::class, 'decline']);
    Route::delete('/friendships/{user:id}', [FriendshipController::class, 'destroy']);

    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::get('/api/conversations/{conversation}', [ChatController::class, 'show'])->name('conversations.show');
    Route::post('/api/conversations', [ChatController::class, 'store'])->name('conversations.store');
    Route::post('/api/conversations/{conversation}/messages', [ChatController::class, 'sendMessage'])->name('conversations.messages.store');
    Route::patch('/api/conversations/{conversation}/read', [ChatController::class, 'markAsRead'])->name('conversations.read');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/api/update-profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/api/update-profile/{uuid}', [ProfileController::class, 'update'])->name('profile.update.uuid');
    Route::delete('/api/users/{uuid}', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('/api/toggle-displayed-badge', [ProfileController::class, 'toggleDisplayedBadge'])->name('profile.toggle-badge');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
