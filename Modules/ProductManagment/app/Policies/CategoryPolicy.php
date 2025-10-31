<?php

namespace Modules\ProductManagment\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ProductManagment\Models\Category;

class CategoryPolicy
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
    public function view(User $user, Category $category)
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
     * @return void
     */
    public function create(User $user)
    {
        //
    }

    /**
     * Summary of update
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return void
     */
    public function update(User $user, Category $category)
    {
        //
    }

    /**
     * Summary of delete
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return void
     */
    public function delete(User $user, Category $category)
    {
        //
    }

    /**
     * Summary of restore
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return void
     */
    public function restore(User $user, Category $category)
    {
        //
    }

    /**
     * Summary of forceDelete
     * @param \App\Models\User $user
     * @param \Modules\ProductManagment\Models\Category $category
     * @return void
     */
    public function forceDelete(User $user, Category $category)
    {
        //
    }
}
