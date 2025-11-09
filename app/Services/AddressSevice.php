<?php

namespace App\Services;

use App\Models\Address;
use App\Traits\CacheTrait;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AddressSevice extends BaseService
{

    use CacheTrait;
    /**
     * Summary of __construct
     * @param \App\Models\Address $address
     */
    public function __construct(Address $address)
    {
        parent::__construct($address);
    }


    /**
     * Summary of getAll
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Model[]|\Traversable<int|string, \Illuminate\Database\Eloquent\Model>
     */
    public function getAll(array $filters = []): iterable
    {

        $user = Auth::user();
        $userKey = $user ? $user->id . '_' . implode(',', $user->roles->pluck('name')->toArray()) : 'guest';
        $cacheKey = "address_{$userKey}" . (empty($filters) ? "" : "_" . md5(json_encode($filters)));

        return Cache::tags(['address'])->remember($cacheKey, now()->addDays(7), function () use ($filters) {
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
        try {
            $data['user_id'] = Auth::user()->id;
            $address = parent::store($data);
            $this->cacheFlush('address');
            return $address->load('user');

        } catch (Exception $e) {
            Log::error('Fail To Add Address .' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Summary of get
     * @param mixed $address
     * @return Model
     */
    public function get($address): Model
    {
        $address = parent::get($address);
        return $address->load('user');
    }

    /**
     * Summary of update
     * @param array $data
     * @param mixed $address
     * @return Model
     */
    public function update(array $data, $address): Model
    {
        try {
            $adress = parent::update($data, $address);
            $this->cacheFlush('address');
            return $adress->load('user');
        } catch (Exception $e) {
            Log::error('Fail To Update Address .' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Summary of destroy
     * @param mixed $address
     * @return bool
     */
    public function destroy($address): bool
    {
        $this->cacheFlush('address');
        return parent::destroy($address);
    }
}
