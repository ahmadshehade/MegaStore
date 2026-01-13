<?php

namespace Modules\ProductManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

class ProductVariantPolicy
{
    use HandlesAuthorization;


    /**
     * Summary of before
     * @param User $user
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
            UserRoles::User->value
        ]);
    }

    /**
     * Summary of view
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function view(User $user, ProductVariant $productVariant)
    {
        return $user->hasAnyRole([
            UserRoles::Customer->value,
            UserRoles::Seller->value,
            UserRoles::User->value
        ]);
    }

    /**
     * Summary of create
     * @param User $user
     * @return bool
     */
    public function create(User $user)
    {

        return $user->hasRole(UserRoles::Seller->value);
    }


    /**
     * Summary of update
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function update(User $user, ProductVariant $productVariant)
    {
        return $user->hasRole(UserRoles::Seller->value) &&
            $user->id === $productVariant->product->created_by;
    }

    /**
     * Summary of delete
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function delete(User $user, ProductVariant $productVariant)
    {
        return $user->hasRole(UserRoles::Seller->value) &&
            $user->id === $productVariant->product->created_by;
    }

    /**
     * Summary of restore
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function restore(User $user, Product $product)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param User $user
     * @param Product $product
     * @return bool
     */
    public function forceDelete(User $user, Product $product)
    {
        return false;
    }
}
