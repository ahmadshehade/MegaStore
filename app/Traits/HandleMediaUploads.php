<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

trait HandleMediaUploads
{
    /**
     * Upload mixed media inputs and return created Media models.
     *
     * Supported items:
     * - UploadedFile instances
     * - local filesystem paths (string)
     * - remote URLs (string starting with http/https)
     * - data URI base64 images (data:image/...)
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $files
     * @param  string $collectionName
     * @return Media[]  Array of created Media models
     */
    public function handleMediaUploadsReturning($model, array $files, string $collectionName = 'default'): array
    {
        $created = [];

        foreach ($files as $file) {
            try {
                // UploadedFile from Request
                if ($file instanceof UploadedFile) {
                    $media = $model->addMedia($file)->toMediaCollection($collectionName);
                    $created[] = $media;
                    continue;
                }

                if (!is_string($file)) {
                    Log::warning('Unsupported media format (not string nor UploadedFile).', ['type' => gettype($file)]);
                    continue;
                }

                $trimmed = trim($file);

                // Base64 Data URI
                if (preg_match('/^data:(image\/[a-zA-Z0-9.+-]+);base64,/', $trimmed)) {
                    $base64 = substr($trimmed, strpos($trimmed, ',') + 1);
                    $media = $model->addMediaFromBase64($base64)
                                   ->usingFileName(uniqid('img_') . '.' . $this->guessExtensionFromDataUri($trimmed))
                                   ->toMediaCollection($collectionName);
                    $created[] = $media;
                    continue;
                }

                // Remote URL
                if (filter_var($trimmed, FILTER_VALIDATE_URL)) {
                    // Note: you may want to restrict allowed hosts / timeouts in production
                    $media = $model->addMediaFromUrl($trimmed)->toMediaCollection($collectionName);
                    $created[] = $media;
                    continue;
                }

                // Local file path on server
                if (file_exists($trimmed) && is_readable($trimmed)) {
                    $media = $model->addMedia($trimmed)->toMediaCollection($collectionName);
                    $created[] = $media;
                    continue;
                }

                Log::warning('Unsupported media string format', ['value' => Str::limit($trimmed, 200)]);
            } catch (\Exception $e) {
                Log::error('Media upload failed', [
                    'message' => $e->getMessage(),
                    'file_value' => is_string($file) ? Str::limit($file, 200) : gettype($file),
                ]);
            }
        }

        return $created;
    }

    /**
     * Delete media records by IDs (this will also remove files from storage via Spatie).
     *
     * @param int[] $ids
     * @return void
     */
    protected function deleteMediaByIds(array $ids): void
    {
        if (empty($ids)) {
            return;
        }

        try {
            $medias = Media::whereIn('id', $ids)->get();
            foreach ($medias as $m) {
                try {
                    $m->delete(); // this removes DB record + physical files (and conversions)
                } catch (\Throwable $e) {
                    Log::error('Failed deleting media id ' . $m->id, ['error' => $e->getMessage()]);
                }
            }
        } catch (\Throwable $e) {
            Log::error('Failed fetching medias for deletion', ['ids' => $ids, 'error' => $e->getMessage()]);
        }
    }

    /**
     * Replace a media collection by uploading new files then deleting the old ones.
     * This method:
     *  - collects old media ids first
     *  - uploads new files (returns created Media[])
     *  - deletes old media by ids (physically and DB)
     *
     * @param string $collectionName
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $files
     * @return Media[]  list of new Media models
     */
    public function replaceMediaCollectionByReAdding(string $collectionName, $model, array $files): array
    {
        // 1) collect old IDs BEFORE uploading new files
        $oldIds = $model->getMedia($collectionName)->pluck('id')->toArray();

        // 2) upload new files to the same collection (or change to temp collection if you prefer)
        $newMedias = $this->handleMediaUploadsReturning($model, $files, $collectionName);

        // 3) delete old medias by IDs (physically removed from storage)
        //    we delete only oldIds we saved earlier to avoid removing newly created media
        $this->deleteMediaByIds($oldIds);

        return $newMedias;
    }

    /**
     * Clear a media collection completely.
     *
     * @param string $collectionName
     * @param \Illuminate\Database\Eloquent\Model $model
     * @return void
     */
    public function deleteMediaCollection(string $collectionName, $model): void
    {
        $model->clearMediaCollection($collectionName);
    }

    /**
     * Guess extension from a data URI (fallback to png)
     *
     * @param string $dataUri
     * @return string
     */
    protected function guessExtensionFromDataUri(string $dataUri): string
    {
        if (preg_match('/^data:(image\/([a-zA-Z0-9.+-]+));base64,/', $dataUri, $m)) {
            return $m[2] ?? 'png';
        }
        return 'png';
    }
}
