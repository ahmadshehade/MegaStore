<?php

namespace Modules\PaymentManagement\Services;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Modules\PaymentManagement\Models\Refund;

use function Symfony\Component\Clock\now;

class RefundService extends BaseService
{


    public function __construct(Refund $model)
    {
        return parent::__construct($model);
    }
    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user ? $user->id . implode($user->roles()->pluck('name')->toArray()) : "guest";
        $cacheKey = $userKey . "_" . ((empty($filters) ? "" : md5(json_encode($filters))));
        return Cache::tags(['refunds'])->remember($cacheKey, 2, function () use ($filters) {
            return parent::getAll($filters)->load(['payment', 'invoice', 'ledgerEntries']);
        });
    }

    /**
     * Summary of get
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model)
            ->load(['payment', 'invoice', 'ledgerEntries']);
    }
}
