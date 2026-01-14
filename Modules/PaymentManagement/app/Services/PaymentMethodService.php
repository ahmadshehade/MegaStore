<?php

namespace Modules\PaymentManagement\Services;

use App\Services\BaseService;
use App\Traits\CacheTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\PaymentManagement\Models\PaymentMethod;

class PaymentMethodService extends BaseService
{
    use CacheTrait;

    public function __construct(PaymentMethod $model)
    {
        parent::__construct($model);
    }

    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user ? $user->id . '-' . implode('-', $user->roles->pluck('name')->toArray()) : 'guest';
        $cacheKey = "paymentMethod_" . $userKey . (empty($filters) ? "" : md5(json_encode($filters)));


        return Cache::tags(['paymentMethods'])->remember($cacheKey, now()->addMonths(2), function () use ($filters) {
            return parent::getAll($filters);
        });
    }

    /**
     * Summary of store
     * @param array $data
     * @return Model
     */
    public function store(array $data): Model
    {
        $paymentMethod = parent::store($data);
        $this->cacheFlush('paymentMethods');
        return $paymentMethod;
    }

    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model);
    }

    public function update(array $data, Model $model): Model
    {
        $this->cacheFlush('paymentMethods');
        return parent::update($data, $model);
    }

    /**
     * Summary of destroy
     * @param Model $model
     * @return bool
     */
    public function destroy(Model $model): bool
    {
        $this->cacheFlush('paymentMethods');
        return parent::destroy($model);
    }
}
