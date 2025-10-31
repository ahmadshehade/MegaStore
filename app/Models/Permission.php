<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Permission as spatiPermission;

class Permission extends spatiPermission
{

    /**
     * Summary of createdAt
     * @return Attribute
     */
    protected function createdAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d-m-y', strtotime($value))
        );
    }

    /**
     * Summary of updatedAt
     * @return Attribute
     */
    protected function updatedAt(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => date('d-m-y', strtotime($value))
        );
    }
}
