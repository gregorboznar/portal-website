<?php

namespace App\Http\Controllers;

use App\Services\ImageUploadService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
  public function __construct(
    private ImageUploadService $imageUploadService
  ) {}

  public function upload(Request $request): JsonResponse
  {
    $request->validate([
      'image' => 'required|image|max:10240', // 10MB max
      'type' => 'string|in:posts,users,general',
    ]);

    try {
      $type = $request->input('type', 'general');
      $image = $this->imageUploadService->uploadForType($request->file('image'), $type);

      $imageData = [
        'id' => $image->id,
        'url' => asset('storage/' . $image->path),
        'filename' => $image->filename,
        'original_filename' => $image->original_filename,
        'width' => $image->width,
        'height' => $image->height,
        'optimizations' => $this->formatOptimizations($image->optimizations),
      ];

      return response()->json([
        'success' => true,
        'image' => $imageData,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload image',
      ], 500);
    }
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

  public function uploadProfile(Request $request): JsonResponse
  {
    $request->validate([
      'profile_image' => 'required|image|max:10240',
    ]);

    try {
      $user = $request->user();
      $image = $this->imageUploadService->uploadForModel($request->file('profile_image'), $user);
      $image->update(['type' => 'profile']);

      $imageData = [
        'id' => $image->id,
        'url' => asset('storage/' . $image->optimizations['medium']['path']),
        'type' => 'profile',
      ];

      return response()->json([
        'success' => true,
        'image' => $imageData,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload profile image',
      ], 500);
    }
  }

  public function uploadCover(Request $request): JsonResponse
  {
    $request->validate([
      'cover_image' => 'required|image|max:10240',
    ]);

    try {
      $user = $request->user();
      $image = $this->imageUploadService->uploadForModel($request->file('cover_image'), $user);
      $image->update(['type' => 'cover']);

      $imageData = [
        'id' => $image->id,
        'url' => asset('storage/' . $image->optimizations['large']['path']),
        'type' => 'cover',
      ];

      return response()->json([
        'success' => true,
        'image' => $imageData,
      ]);
    } catch (\Exception $e) {
      return response()->json([
        'success' => false,
        'message' => 'Failed to upload cover image',
      ], 500);
    }
  }
}
