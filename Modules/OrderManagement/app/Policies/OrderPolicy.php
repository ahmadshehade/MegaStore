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
     * @param User $user
     *
     */
    public function before(User $user)
    {
        if ($user->hasRole(UserRoles::SuperAdmin->value)) {
            return true;
        }
    }

    /**
     * Summary of viewAny
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        if($user->hasRole(UserRoles::Seller->value)) {
            return true;
        }
        if($user->hasRole(UserRoles::Customer->value)) {
            return true;
        }
        return false;
    }

    /**
     * Summary of view
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function view(User $user, Order $order)
    {
        if ($user->hasRole(UserRoles::Customer->value) && $order->customer_id === $user->id) {
            return true;
        }
        if ($user->hasRole(UserRoles::Seller->value)) {
            return $order->items()
                ->whereHas('productVariant.product', function ($q) use ($user) {
                    $q->where('created_by', $user->id);
                })
                ->exists();
        }
        return false;
    }

    /**
     * Summary of create
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasRole(UserRoles::Customer->value);
    }

    /**
     * Summary of update
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function update(User $user, Order $order): bool
    {
        if (!$user->hasRole(UserRoles::Customer->value)) {
            return false;
        }
        if ($user->id !== $order->customer_id) {
            return false;
        }
        return !$order->invoice || $order->invoice->status === 'pending';
    }


    /**
     * Summary of delete
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function delete(User $user, Order $order)
    {
        if (!$user->hasRole(UserRoles::Customer->value)) {
            return false;
        }
        if ($user->id !== $order->customer_id) {
            return false;
        }
        return !$order->invoice || $order->invoice->status === 'pending';
    }
    /**
     * Summary of restore
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function restore(User $user, Order $order)
    {
        return false;
    }
    /**
     * Summary of forceDelete
     * @param User $user
     * @param Order $order
     * @return bool
     */
    public function forceDelete(User $user, Order $order)
    {
        return false;
    }
}
