<?php

namespace Modules\ProductManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use App\Traits\HandleMediaUploads;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\ProductManagement\Models\Category;

class CategoryService extends BaseService
{

    use CacheTrait, HandleMediaUploads;
    /**
     * Summary of __construct
     * @param Category $category
     */
    public  function __construct(Category $category)
    {
        parent::__construct($category);
    }
    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
   public  function getAll(array $filters = [], ?Closure $scope = null): iterable
   {
       $user = Auth::user();
        $userkey = $user ? $user->id . "_" . implode(',', $user->roles->pluck('name')->toArray()) : 'guest';
        $cacheKey = "categories_" . $userkey . (empty($filters) ? "" : "_" . md5(json_encode($filters)));
        return Cache::tags(['categories'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
             $categories = parent::getAll($filters);
              return $categories->load(['parent', 'children', 'media']);
        });
   }
    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $category = parent::store($data);
        if (!empty($data['images'])) {
            $this->handleMediaUploadsReturning($category, $data['images'], 'category_images');
        }
        $this->cacheFlush('categories');
        return $category->load(['parent', 'children','media']);
    }
    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        $category = parent::get($model);
        return $category->load(['children', 'parent','media']);
    }
    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        $category = parent::update($data, $model);
        if (!empty($data['images'])) {
            $this->replaceMediaCollectionByReAdding('category_images', $category, $data['images']);
        }
        $this->cacheFlush('categories');
        return $category->load(['children', 'parent','media']);
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
        parent::destroy($model);
        $this->cacheFlush('categories');
        return true;
    }
}
