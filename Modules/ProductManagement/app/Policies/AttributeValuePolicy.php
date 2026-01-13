<?php

namespace Modules\ProductManagement\Policies;

use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Modules\ProductManagement\Models\AttributeValue;

class AttributeValuePolicy
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
        return  $user->hasAnyRole([

            UserRoles::Seller->value,
        ]);
    }

    /**
     * Summary of view
     * @param User $user
     * @param AttributeValue $attributeValue
     * @return bool
     */
    public function view(User $user, AttributeValue $attributeValue)
    {
        return $user->hasAnyRole([
            UserRoles::Seller->value,
        ]);
    }

    /**
     * Summary of create
     * @param User $user
     * @return bool
     */
    public function  create(User $user)
    {
        return $user->hasRole(UserRoles::Seller->value) &&
            $user->attributes()->exists();
    }

    /**
     * Summary of update
     * @param User $user
     * @param AttributeValue $attributeValue
     * @return bool
     */
    public function update(User $user, AttributeValue $attributeValue)
    {
        return $user->hasRole(UserRoles::Seller->value) &&
            $user->id === $attributeValue->attribute->creator->id;
    }

    /**
     * Summary of delete
     * @param User $user
     * @param AttributeValue $attributeValue
     * @return bool
     */
    public function delete(User $user, AttributeValue $attributeValue)
    {
       return $user->hasRole(UserRoles::Seller->value) &&
            $user->id === $attributeValue->attribute->creator->id;
    }

    /**
     * Summary of restore
     * @param User $user
     * @param AttributeValue $attributeValue
     * @return bool
     */
    public function restore(User $user, AttributeValue $attributeValue)
    {
        return false;
    }

    /**
     * Summary of forceDelete
     * @param User $user
     * @param AttributeValue $attributeValue
     * @return bool
     */
    public function forceDelete(User $user, AttributeValue $attributeValue)
    {
        return false;
    }

}
