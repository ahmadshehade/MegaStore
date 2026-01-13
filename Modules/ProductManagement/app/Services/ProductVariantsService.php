<?php

namespace Modules\ProductManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use App\Traits\HandleMediaUploads;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\ProductManagement\Models\ProductVariant;

class ProductVariantsService extends BaseService
{
    use CacheTrait, HandleMediaUploads;
    /**
     * Summary of __construct
     * @param ProductVariant $productVariant
     */
    public function __construct(ProductVariant $productVariant)
    {
        parent::__construct($productVariant);
    }


    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user ? $user->id . implode(",", $user->roles->pluck('name')->toArray()) : "guest";
        $cacheKey = "product_variants_" . $userKey . (empty($filters) ? "" : "" . md5(json_encode($filters)));
        return Cache::tags(['productvariants'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
            return parent::getAll($filters)->load(['product']);
        });
    }

    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model)->load('product');
    }

    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $variants = parent::store($data);
        if (!empty($data['images'])) {
            $this->handleMediaUploadsReturning($variants, $data['images'], 'product_variant');
        }
        $this->cacheFlush('productvariants');
        return $variants->load(['product', 'media']);
    }

    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        $variants = parent::update($data, $model);
        if ($data['images'] && is_array($data['images'])) {
            $this->replaceMediaCollectionByReAdding('product_variant', $variants, $data['images']);
        }
        $this->cacheFlush('productvariants');
        return $variants->load(['product', 'media']);
    }


    /**
     * Summary of destroy
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool
    {

        if ($model->hasMedia()) {
            $model->clearMediaCollection();
        }
        $this->cacheFlush('productvariants');
        return parent::destroy($model);
    }
}
