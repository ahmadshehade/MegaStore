<?php

namespace Modules\PaymentManagement\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class PaymentScope implements Scope
{
    /**
     * Summary of apply
     * @param Builder $builder
     * @param Model $model
     * @return void
     */
    public function apply(Builder $builder, Model $model): void
    {
        $user = Auth::user();
        if (!$user) return;


        if ($user->hasRole('admin')) {
            return;
        }


        if ($user->hasRole('customer')) {
            $builder->whereHas('invoice.order', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }
    }
}
