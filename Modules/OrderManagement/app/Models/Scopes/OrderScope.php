<?php

namespace Modules\OrderManagement\Models\Scopes;

use App\Enum\UserRoles;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class OrderScope implements Scope
{

    /**
     * Summary of apply
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {

        if (!Auth::user()) {
            return;
        }
        $user = Auth::user();




        if ($user->hasRole(UserRoles::SuperAdmin->value)) {
            return;
        }


        if ($user->hasRole(UserRoles::Customer->value)) {
            $builder->where('customer_id', $user->id);
            return;
        }


        if ($user->hasRole(UserRoles::Seller->value)) {
            $builder->whereHas('items.productVariant.product', function ($q) use ($user) {
                $q->where('created_by', $user->id);
            });
            return;
        }


        $builder->whereRaw('1 = 0');
    }
}
