<?php

namespace App\Traits;

use App\Models\Image;
use Exception;
use Illuminate\Support\Facades\Storage;
use Str;


trait ImageManagement
{


 
    /**
     * Summary of uploadImages
     * @param array $files
     * @param string $disk
     * @param string $imageable_type
     * @param int $imageable_id
     * @return array
     */
    public function uploadImages(array $files, string $disk, string $imageable_type, int $imageable_id): array
    {
        $uploadedImages = [];
        $dataToInsert = [];

        foreach ($files as $file) {
            if (!$file || !method_exists($file, 'getClientOriginalName')) {
                continue;
            }

            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $filename = $originalName . '_' . \Illuminate\Support\Str::random(12) . '.' . $extension;
            $folder = class_basename($imageable_type);

            $path = $file->storeAs($folder, $filename, $disk);
            $url = Storage::disk($disk)->url($path);

            $dataToInsert[] = [
                'imageable_id' => $imageable_id,
                'imageable_type' => $imageable_type,
                'mime_types' => $file->getClientMimeType(),
                'filename' => $path,
                'url' => $url,
                'size' => $file->getSize(),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        if (!empty($dataToInsert)) {
            Image::insert($dataToInsert);
            $uploadedImages = Image::where('imageable_id', $imageable_id)
                ->where('imageable_type', $imageable_type)
                ->get();
        }

        return [$uploadedImages];
    }





    /**
     * Summary of deleteImages
     * @param mixed $imageable
     * @param mixed $disk
     * @return array
     */
    public function deleteImages($imageable, $disk)
    {
        $images = $imageable->images ?? collect();
        if ($images->isEmpty()) {
            return [];
        }
        $paths = $images->pluck('filename')->toArray();
        Storage::disk($disk)->delete($paths);
        $images->pluck('id')->chunk(100)->each(function ($chunk) {
            Image::whereIn('id', $chunk)->delete();
        });

        return $images;
    }



    /**
     * Summary of deleteImagesWithBackup
     * @param mixed $imageable
     * @param string $disk
     * @return array{backupPaths: array, deletedImages: array|array{backupPaths: string[], deletedImages: array}}
     */
    public function deleteImagesWithBackup($imageable, string $disk): array
    {
        $images = $imageable->images ?? collect();
        $deletedImages = [];
        $backupPaths = [];

        foreach ($images as $image) {
            $path = $image->filename;

            $backupPath = 'backup/' . basename($path);
            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->copy($path, $backupPath);
            }
            $backupPaths[$image->id] = $backupPath;

            if (Storage::disk($disk)->exists($path)) {
                Storage::disk($disk)->delete($path);
            }
            $deletedImages[] = $image;
            $image->delete();
        }

        return [
            'deletedImages' => $deletedImages,
            'backupPaths' => $backupPaths,
        ];
    }


    /**
     * Summary of restoreImagesFromBackup
     * @param array $backupPaths
     * @param string $disk
     * @param mixed $imageable
     * @return void
     */
    public function restoreImagesFromBackup(array $backupPaths, string $disk, $imageable)
    {
        foreach ($backupPaths as $imageId => $backupPath) {
            $originalPath = $imageable->images()->withTrashed()->find($imageId)?->filename;
            if ($originalPath && Storage::disk($disk)->exists($backupPath)) {
                Storage::disk($disk)->copy($backupPath, $originalPath);
            }
        }
    }

}