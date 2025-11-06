<?php

namespace Modules\OrderManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\OrderManagement\Models\Shipping;

class ShippingPolicy
{
    use HandlesAuthorization;

    /**
     * Summary of before
     * @param \App\Models\User $user
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
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user)
    {
        return $user->hasAnyRole([
            UserRoles::Seller,
            UserRoles::User,
            UserRoles::Customer,
        ]);
    }

    /**
     * Summary of view
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Shipping $shipping
     * @return bool
     */
    public function view(User $user, Shipping $shipping)
    {
        return $user->hasAnyRole([
            UserRoles::Seller,
            UserRoles::User,
            UserRoles::Customer,
        ]);
    }

    /**
     * Summary of create
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(User $user)
    {
        return false;
    }

    /**
     * Summary of update
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Shipping $shipping
     * @return bool
     */
    public function update(User $user, Shipping $shipping)
    {
        return false;
    }


    /**
     * Summary of delete
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Shipping $shipping
     * @return bool
     */
    public function delete(User $user, Shipping $shipping)
    {
        return false;
    }


    /**
     * Summary of restore
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Shipping $shipping
     * @return bool
     */
    public function restore(User $user, Shipping $shipping)
    {
        return false;
    }


    /**
     * Summary of forceDelete
     * @param \App\Models\User $user
     * @param \Modules\OrderManagement\Models\Shipping $shipping
     * @return bool
     */
    public function forceDelete(User $user, Shipping $shipping)
    {
        return false;
    }
}

