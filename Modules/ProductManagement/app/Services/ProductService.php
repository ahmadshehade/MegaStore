<?php

namespace Modules\ProductManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use App\Traits\HandleMediaUploads;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\ProductManagement\Models\Product;

class ProductService extends BaseService
{

    use CacheTrait, HandleMediaUploads;
    /**
     * Summary of __construct
     * @param Product $product
     */
    public function __construct(Product $product)
    {
        parent::__construct($product);
    }

    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
   public function getAll(array $filters = [], ?Closure $scope = null): iterable
   {
        $user = Auth::user();
        if ($user) {
            $rolesString = collect($user->roles->pluck('name'))->implode(',');
            $userKey = $user->id . '|' . $rolesString;
        } else {
            $userKey = 'guest';
        }
        $cacheKey = 'products_' . $userKey . (empty($filters) ? '' : '_' . md5(json_encode($filters)));
        return Cache::tags(['products'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
            return parent::getAll($filters)->load(['creator', 'category', 'media']);
        });
   }


    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function  store(array $data): Model
    {
        $product = parent::store($data);
        $images = Arr::get($data, 'images', []);

        if (is_array($images) && count($images) > 0) {
            $this->handleMediaUploadsReturning(
                $product,
                $images,
                'product_collection'
            );
        }
        $this->cacheFlush('products');
        return $product->load(['creator', 'category', 'media']);
    }


    /**
     * Summary Of Get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model)->load(['creator', 'category', 'media']);
    }


    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        $product = parent::update($data, $model);
        if (!empty($data['images'])) {
            $this->replaceMediaCollectionByReAdding('product_collection', $product, $data['images']);
        }
        $this->cacheFlush('products');
        return $product->load(['creator', 'category', 'media']);
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
        $this->cacheFlush('products');
        return parent::destroy($model);
    }
}
