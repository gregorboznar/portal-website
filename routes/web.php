<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\ImageController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('friends', function () {
    return Inertia::render('Friends');
})->middleware(['auth', 'verified'])->name('friends');

Route::get('events', function () {
    return Inertia::render('Events');
})->middleware(['auth', 'verified'])->name('events');

Route::get('whitney-wire-report', function () {
    return Inertia::render('WhitneyWireReport');
})->middleware(['auth', 'verified'])->name('whitney-wire-report');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/api/posts', [PostController::class, 'index']);
    Route::post('/api/posts', [PostController::class, 'store']);
    Route::post('/api/posts/{post}/like', [PostController::class, 'toggleLike']);
    Route::get('/api/posts/{post}', [PostController::class, 'show']);
    Route::post('/api/images/upload', [ImageController::class, 'upload']);
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
