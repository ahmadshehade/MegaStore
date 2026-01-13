<?php

namespace Modules\PaymentManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\PaymentManagement\Models\Payment;

class PaymentPolicy
{
    use HandlesAuthorization;

    /**
     * Summary of before
     * @param User $user
     * @return bool|null
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole(UserRoles::SuperAdmin->value)) {
            return true;
        }

        return null;
    }

    /**
     * Summary of viewAny
     * @param User $user
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $user->hasRole(UserRoles::Customer->value);
    }

    /**
     * Summary of view
     * @param User $user
     * @param Payment $payment
     * @return bool
     */
    public function view(User $user, Payment $payment): bool
    {
        $order = $payment->invoice?->order;

        return $order
            && $user->hasRole(UserRoles::Customer->value)
            && $order->customer_id === $user->id;
    }

    /**
     * Summary of create
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        if (! $user->hasRole(UserRoles::Customer->value)) {
            return false;
        }

        return $user->orders()
            ->whereHas('invoice', function ($q) {
                $q->whereNotIn('status', ['paid', 'cancelled','revised']);
            })
            ->exists();
    }

    /**
     * Summary of update
     * @param User $user
     * @param Payment $payment
     * @return bool
     */
    public function update(User $user, Payment $payment): bool
    {
        $invoice = $payment->invoice;
        $order   = $invoice?->order;

        return $order
            && $user->hasRole(UserRoles::Customer->value)
            && $order->customer_id === $user->id;
    }
    /**
     * Summary of delete
     * @param User $user
     * @param Payment $payment
     * @return bool
     */
    public function delete(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Summary of restore
     * @param User $user
     * @param Payment $payment
     * @return bool
     */
    public function restore(User $user, Payment $payment): bool
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param User $user
     * @param Payment $payment
     * @return bool
     */
    public function forceDelete(User $user, Payment $payment): bool
    {
        return false;
    }
}
