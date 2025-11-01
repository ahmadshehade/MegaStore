<?php

namespace Modules\ProductManagment\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SyncCategoryImagesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $modelClass;     
    public $modelId;
    public $tempPaths;       
    public $collectionName;  
    public $disk;           

    public $tries = 3;
    public $timeout = 120;

    public function __construct(string $modelClass, $modelId, array $tempPaths, string $collectionName = 'categories', string $disk = 'media')
    {
        $this->modelClass = $modelClass;
        $this->modelId = $modelId;
        $this->tempPaths = $tempPaths;
        $this->collectionName = $collectionName;
        $this->disk = $disk;
    }

    /**
     * Summary of handle
     * @return void
     */
    public function handle()
    {
        $modelClass = $this->modelClass;
        $model = $modelClass::find($this->modelId);

        if (!$model) {
            Log::warning("SyncCategoryImagesJob: model not found {$this->modelClass} id={$this->modelId}");
            return;
        }

        try {
            if (method_exists($model, 'hasMedia') && $model->hasMedia($this->collectionName)) {
                $model->clearMediaCollection($this->collectionName);
            }
        } catch (\Throwable $e) {
            Log::error("SyncCategoryImagesJob: clearMedia failed: " . $e->getMessage());
        }

        foreach ($this->tempPaths as $path) {
            try {
                if (Storage::disk($this->disk)->exists($path)) {
                    $model->addMediaFromDisk($path, $this->disk)
                          ->toMediaCollection($this->collectionName);
                    Storage::disk($this->disk)->delete($path);
                } else {
                    Log::warning("SyncCategoryImagesJob: temp file not found on disk {$this->disk}: {$path}");
                }
            } catch (\Throwable $e) {
                Log::error("SyncCategoryImagesJob: failed to addMediaFromDisk {$path}: " . $e->getMessage());
            }
        }
    }
}
