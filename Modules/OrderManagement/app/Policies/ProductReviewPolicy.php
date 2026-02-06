<?php

namespace Modules\OrderManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\OrderManagement\Models\ProductReview;

class ProductReviewPolicy
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
        return $user->hasAnyRole([
            UserRoles::Customer->value,
            UserRoles::Seller->value,
        ]);
    }

    /**
     * Summary of view
     * @param User $user
     * @param ProductReview $order
     * @return bool
     */
    public function view(User $user, ProductReview $productReview)
    {
        if ($user->hasRole(UserRoles::Customer->value)) {
            return $productReview->is_approved || $productReview->user_id === $user->id;
        }
        if ($user->hasRole(UserRoles::Seller->value)) {
            return  $productReview->product->created_by === $user->id;
        }
        return false;
    }

    /**
     * Summary of create
     * @param User $user
     * @return bool
     */
    public function create(User $user): bool
    {
        return $user->hasRole(UserRoles::Customer->value);
    }


    /**
     * Summary of update
     * @param User $user
     * @param ProductReview $order
     * @return bool
     */
    public function update(User $user, ProductReview $productReview): bool
    {
        return $user->hasRole(UserRoles::Customer->value)
            && $productReview->user_id === $user->id;
    }

    /**
     * Summary of delete
     * @param User $user
     * @param ProductReview $order
     * @return bool
     */
    public function delete(User $user, ProductReview $productReview)
    {
        return $user->hasRole(UserRoles::Customer->value)
            && $productReview->user_id === $user->id;
    }
    /**
     * Summary of restore
     * @param User $user
     * @param ProductReview $order
     * @return bool
     */
    public function restore(User $user, ProductReview $productReview)
    {
        return false;
    }
    /**
     * Summary of forceDelete
     * @param User $user
     * @param ProductReview $order
     * @return bool
     */
    public function forceDelete(User $user, ProductReview $productReview)
    {
        return false;
    }
}
