<?php

namespace Modules\OrderManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\OrderManagement\Models\Order;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Summary of before
     * @param \App\Models\User $user
     *
     */
    public function before(User $user)
    {
        if ($user->hasRole(UserRoles::SuperAdmin)) {
            return true;
        }
    }
    /**
     * Summary of viewAny
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole([
            UserRoles::Customer->value,
            UserRoles::Seller->value
        ]);
    }

    /**
     * Summary of view
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Order $order
     * @return bool
     */
    public function view(User $user, Order $order)
    {
        if ($user->hasRole(UserRoles::Customer->value) && $user->id == $order->user_id) {
            return true;
        }
        if ($user->hasRole(UserRoles::Seller->value)) {
            return $order->items()->whereHas('product', function ($query) use ($user) {
                $query->where('seller_id', $user->id);
            })->exists();
        }
        return false;
    }

    /**
     * Summary of create
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasRole(UserRoles::Customer->value);
    }

    /**
     * Summary of update
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Order $order
     * @return bool
     */
    public function update(User $user, Order $order)
    {
        if ($user->hasRole(UserRoles::Customer->value) && $user->id == $order->user_id) {
            return true;
        }
        return false;
    }

    /**
     * Summary of delete
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Order $order
     * @return bool
     */
    public function delete(User $user, Order $order)
    {
        if ($user->hasRole(UserRoles::Customer->value) && $user->id == $order->user_id) {
            return true;
        }
        return false;
    }

    /**
     * Summary of restore
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Order $order
     * @return bool
     */
    public function restore(User $user, Order $order)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Order $order
     * @return bool
     */
    public function forceDelete(User $user, Order $order)
    {
        return false;
    }
}
