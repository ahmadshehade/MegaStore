<?php

namespace Modules\OrderManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\OrderManagement\Models\Discount;

class DiscountService extends BaseService
{

    use CacheTrait;
    /**
     * Summary of __construct
     * @param Discount $model
     */
    public function __construct(Discount $model)
    {
        return parent::__construct($model);
    }
    /**
     * Summary of getAll
     * @param array $filters
     * @return Model[]|\Traversable<int|string, Model>
     */
    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user ? $user->id : 'non';
        $cacheKey = 'discounts_' . $userKey . (empty($filters) ? "_" : md5(json_encode($filters)));
        return Cache::tags(['discounts'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
            return  parent::getAll($filters);
        });
    }

    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $data['created_by'] = Auth::id();
        $this->cacheFlush('discounts');
        return parent::store($data);
    }

    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model)->load('creator');
    }

    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        $this->cacheFlush('discounts');
        return parent::update($data, $model);
    }

    /**
     * Summary of destroy
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool
    {
        $this->cacheFlush('discounts');
        return parent::destroy($model);
    }

 
}
