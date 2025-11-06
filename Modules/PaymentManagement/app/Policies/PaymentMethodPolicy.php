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
     * Summary of viewAll
     * @param \App\Models\User $user
     * @return bool
     */
    public function viewAny(User $user)
    {

        return $user->hasAnyRole([
            UserRoles::User->value,
            UserRoles::Seller->value,
            UserRoles::Customer->value,

        ]);
    }
    /**
     * Summary of view
     * @param \App\Models\User $user
     * @param \Modules\PaymentManagement\Models\PaymentMethod $paymentMethod
     * @return bool
     */
    public function view(User $user, PaymentMethod $paymentMethod)
    {
        return $user->hasAnyRole([
            UserRoles::User->value,
            UserRoles::Seller->value,
            UserRoles::Customer->value,

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
     * @param \Modules\PaymentManagement\Models\PaymentMethod $paymentMethod
     * @return bool
     */
    public function update(User $user, PaymentMethod $paymentMethod)
    {
        return false;
    }

    /**
     * Summary of delete
     * @param \App\Models\User $user
     * @param \Modules\PaymentManagement\Models\PaymentMethod $paymentMethod
     * @return bool
     */
    public function delete(User $user, PaymentMethod $paymentMethod)
    {
        return false;
    }

    /**
     * Summary of restore
     * @param \App\Models\User $user
     * @param \Modules\PaymentManagement\Models\PaymentMethod $paymentMethod
     * @return bool
     */
    public function restore(User $user, PaymentMethod $paymentMethod)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param \App\Models\User $user
     * @param \Modules\PaymentManagement\Models\PaymentMethod $paymentMethod
     * @return bool
     */
    public function forceDelete(User $user, PaymentMethod $paymentMethod)
    {
        return false;
    }
}
