<?php

namespace App\Services;

use App\Models\Image;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class ImageUploadService
{
  public function __construct(
    private ImageManager $imageManager = new ImageManager(new Driver())
  ) {}

  public function upload(UploadedFile $file, $imageable = null): Image
  {
    $filename = $this->generateUniqueFilename($file);
    $tableFolder = $imageable ? $this->getTableFolder($imageable) : 'general';
    $path = "images/{$tableFolder}/original/{$filename}";

    $image = $this->imageManager->read($file->getRealPath());
    $width = $image->width();
    $height = $image->height();

    $optimizations = $this->createOptimizedVersions($image, $filename, $tableFolder);

    Storage::disk('public')->put($path, $image->encode());

    $imageableType = null;
    $imageableId = null;

    if ($imageable) {
      $imageableType = get_class($imageable);
      $imageableId = $imageable->id;
    }

    return Image::create([
      'filename' => $filename,
      'original_filename' => $file->getClientOriginalName(),
      'path' => $path,
      'mime_type' => $file->getMimeType(),
      'size' => $file->getSize(),
      'width' => $width,
      'height' => $height,
      'optimizations' => $optimizations,
      'imageable_type' => $imageableType,
      'imageable_id' => $imageableId,
    ]);
  }

  private function generateUniqueFilename(UploadedFile $file): string
  {
    return Str::uuid() . '.' . $file->getClientOriginalExtension();
  }

  private function getTableFolder($imageable): string
  {
    $className = class_basename($imageable);
    return strtolower($className) . 's';
  }

  private function createOptimizedVersions($image, string $filename, string $tableFolder): array
  {
    $optimizations = [];
    $filenameWithoutExt = pathinfo($filename, PATHINFO_FILENAME);
    $extension = pathinfo($filename, PATHINFO_EXTENSION);

    $sizes = [
      'thumbnail' => ['width' => 150, 'height' => 150],
      'small' => ['width' => 400, 'height' => 400],
      'medium' => ['width' => 800, 'height' => 800],
      'large' => ['width' => 1200, 'height' => 1200],
    ];

    foreach ($sizes as $size => $dimensions) {
      $optimizedImage = clone $image;
      $optimizedImage->scale($dimensions['width'], $dimensions['height']);

      $optimizedFilename = "{$filenameWithoutExt}.{$extension}";
      $optimizedPath = "images/{$tableFolder}/{$size}/{$optimizedFilename}";

      Storage::disk('public')->put($optimizedPath, $optimizedImage->encode());

      $optimizations[$size] = [
        'filename' => $optimizedFilename,
        'path' => $optimizedPath,
        'width' => $optimizedImage->width(),
        'height' => $optimizedImage->height(),
      ];
    }

    return $optimizations;
  }

  public function delete(Image $image): bool
  {
    Storage::disk('public')->delete($image->path);

    if ($image->optimizations) {
      foreach ($image->optimizations as $optimization) {
        Storage::disk('public')->delete($optimization['path']);
      }
    }

    return $image->delete();
  }

  public function uploadStandalone(UploadedFile $file): Image
  {
    return $this->upload($file, null);
  }

  public function uploadForModel(UploadedFile $file, $imageable): Image
  {
    return $this->upload($file, $imageable);
  }

  public function uploadForType(UploadedFile $file, string $type): Image
  {
    $filename = $this->generateUniqueFilename($file);
    $tableFolder = $type;
    $path = "images/{$tableFolder}/original/{$filename}";

    $image = $this->imageManager->read($file->getRealPath());
    $width = $image->width();
    $height = $image->height();

    $optimizations = $this->createOptimizedVersions($image, $filename, $tableFolder);

    Storage::disk('public')->put($path, $image->encode());

    return Image::create([
      'filename' => $filename,
      'original_filename' => $file->getClientOriginalName(),
      'path' => $path,
      'mime_type' => $file->getMimeType(),
      'size' => $file->getSize(),
      'width' => $width,
      'height' => $height,
      'optimizations' => $optimizations,
      'imageable_type' => null,
      'imageable_id' => null,
      'type' => $type,
    ]);
  }
}
