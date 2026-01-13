<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Cache\TaggableStore;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserService
{

    /**
     * Summary of getAll
     *
     */
    /**
     * Get all users with optional filters (supports Redis cache with tags).
     *
     * @param array $filters
     * @return \Illuminate\Database\Eloquent\Collection<int, User>
     * @throws \Exception if Redis not available
     */
    public function getAll(array $filters = [])
    {

        if (!($store = Cache::getStore()) instanceof TaggableStore) {
            throw new Exception('Redis cache is required for this method.');
        }


        if (!empty($filters) && is_array($filters)) {
            ksort($filters);
            $cacheKey = 'users.all.' . md5(json_encode($filters));
        } else {
            $cacheKey = 'users.all';
        }
        return Cache::tags(['users'])->remember($cacheKey, now()->addHour(), function () use ($filters) {
            $query = User::query();
            $filterable = ['name', 'email'];
            foreach ($filters as $field => $value) {
                if (in_array($field, $filterable) && !empty($value)) {
                    $query->where($field, 'like', '%' . $value . '%');
                }
            }
            return $query->get();
        });
    }


    /**
     * Get a specific user.
     */
    public function get(User $user)
    {

        return $user;
    }



    /**
     * Update a user and flush related cache (after success).
     */
    public function update(User $user, array $data)
    {
        try {
            DB::beginTransaction();
            if (array_key_exists('password', $data) && $data['password']) {
                $data['password'] = Hash::make($data['password']);
            }

            $user->update($data);
            $this->flushUsersCache();
            DB::commit();
            return $user;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Fail Update User Info: ' . $e->getMessage());
            throw $e;
        }
    }


    /**
     * Delete a user and flush related cache (returns actual result).
     */
    public function destroy(User $user)
    {
        $user->delete();
        $this->flushUsersCache();
        return true;
    }

    /**
     * Flush users-related cache safely (only if tags supported).
     */
    protected function flushUsersCache(): void
    {
        $store = Cache::getStore();

        if ($store instanceof TaggableStore) {
            Cache::tags(['users'])->flush();
        } else {

            Cache::forget('users.all');
        }
    }
}
