<?php

namespace Modules\OrderManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\OrderManagement\Models\ProductReview;

class ProductReviewService extends BaseService
{
  use CacheTrait;

    /**
     * Summary of __construct
     * @param ProductReview $review
     */
    public function __construct(ProductReview $review)
    {
        parent::__construct($review);
    }

    /**
     * Summary of getAll
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = [], ?Closure $scope = null): iterable
    {
        $user = Auth::user();
        $userKey = $user
            ? $user->id . '_' . implode('_', $user->getRoleNames()->toArray())
            : 'guest';
        $cacheKey = 'product_reviews_' . $userKey . '_' .
            (empty($filters) ? 'no_filters' : md5(json_encode($filters)));
        return Cache::tags('product_reviews')->remember($cacheKey, now()->addDay(), function () use ($filters, $user) {
            return parent::getAll($filters, function ($query) use ($user) {
                if ($user) {
                    $query->visibleFor($user);
                } else {
                    $query->whereRaw('0 = 1');
                }
                $query->with(['product', 'order', 'user']);
            });
        });
    }




    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public  function store(array $data): Model
    {
        $data['user_id'] = Auth::user()->id;
        $review = parent::store($data);
        $this->cacheFlush('product_reviews');
        return  $review->load(['product', 'order', 'user']);
    }

    /**
     * Summary of update
     * @param array $data
     * @param Model $model
     * @return Model
     */
    public function update(array $data, Model $model): Model
    {
        $review = parent::update($data, $model);
        $this->cacheFlush('product_reviews');
        return $review->load(['product', 'order', 'user']);
    }

    /**
     * Summary of destroy
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool
    {
        $this->cacheFlush('product_reviews');
        return parent::destroy($model);
    }
}
