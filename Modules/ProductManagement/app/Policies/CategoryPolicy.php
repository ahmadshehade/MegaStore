<?php

namespace Modules\ProductManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ProductManagement\Models\Category;

class CategoryPolicy
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
        return ($user->hasAnyRole([
            UserRoles::Seller->value,
            UserRoles::Customer->value,
        ]));
    }

    /**
     * Summary of view
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function view(User $user, Category $category)
    {
        return ($user->hasAnyRole([
            UserRoles::Seller->value,
            UserRoles::Customer->value,
        ]));
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
     * @param Category $category
     * @return bool
     */
    public function  update(User $user, Category $category)
    {
        return false;
    }

    /**
     * Summary of delete
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function delete(User $user, Category $category)
    {
        return false;
    }

    /**
     * Summary of restore
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function restore(User $user, Category $category)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param User $user
     * @param Category $category
     * @return bool
     */
    public function forceDelete(User $user, Category $category)
    {
        return false;
    }
}
