<?php

namespace Modules\ProductManagment\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ProductManagment\Models\Product;

class ProductPolicy
{

    use HandlesAuthorization;

    /**
     * Create a new policy instance.
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
            UserRoles::Seller->value,
            UserRoles::User->value,
            UserRoles::Customer->value
        ]);
    }


    /**
     * Summary of view
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return bool
     */
    public function view(User $user, Product $product)
    {
        return $user->hasAnyRole([
            UserRoles::Seller->value,
            UserRoles::User->value,
            UserRoles::Customer->value
        ]);
    }

    /**
     * Summary of create
     * @param \App\Models\User $user
     * @return bool
     */
    public function create(User $user)
    {
        return $user->hasRole(UserRoles::Seller->value);
    }

    /**
     * Summary of update
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return bool
     */
    public function update(User $user, Product $product)
    {
        return ($user->hasRole(UserRoles::Seller->value) &&
            $product->seller_id = $user->id);
    }

    /**
     * Summary of delete
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return bool
     */
    public function delete(User $user, Product $product)
    {
        return ($user->hasRole(UserRoles::Seller->value) &&
            $product->seller_id = $user->id);
    }

    /**
     * Summary of restore
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return bool
     */
    public function restore(User $user, Product $product)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return bool
     */
    public function forceDelete(User $user, Product $product)
    {
        return false;
    }

}
