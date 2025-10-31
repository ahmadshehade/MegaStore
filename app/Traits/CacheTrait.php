<?php 

namespace App\Traits;

use Illuminate\Support\Facades\Cache;

/**
 * Summary of CacheTrait
 */
trait CacheTrait{

    public function cacheFlush( $cacheTag ){
      
        Cache::tags([$cacheTag])->flush();
    }
}