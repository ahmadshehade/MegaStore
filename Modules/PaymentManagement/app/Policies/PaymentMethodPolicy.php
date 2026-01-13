<?php

namespace Modules\PaymentManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\PaymentManagement\Models\PaymentMethod;

class PaymentMethodPolicy
{
    use HandlesAuthorization;

    /**
     * Summary of before
     * @param User $user
     *
     */
    public function before(User $user)
    {
        if ($user->hasRole(UserRoles::Customer->value)) {
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
        return $user->hasRole(UserRoles::Customer->value);
    }

    /**
     * Summary of view
     * @param User $user
     * @param PaymentMethod $payment
     * @return bool
     */
    public function view(User $user, PaymentMethod $payment)
    {
        return $user->hasRole(UserRoles::Customer->value);
    }

    /**
     * Summary of create
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {
        return false;
    }
    /**
     * Summary of update
     * @param User $user
     * @param PaymentMethod $payment
     * @return bool
     */
    public function update(User $user, PaymentMethod $payment)
    {
        return false;
    }
    /**
     * Summary of delete
     * @param User $user
     * @param PaymentMethod $payment
     * @return bool
     */
    public function delete(User $user, PaymentMethod $payment)
    {
        return false;
    }
    /**
     * Summary of restore
     * @param User $user
     * @param PaymentMethod $payment
     * @return bool
     */
    public function restore(User $user, PaymentMethod $payment)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param User $user
     * @param PaymentMethod $payment
     * @return bool
     */
    public function forceDelete(User $user, PaymentMethod $payment)
    {
        return false;
    }
}
