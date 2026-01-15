<?php

namespace App\Models\Base;

use App\Enum\UserRoles;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class BaseModel extends Model
{

    /**
     * Summary of getCreatedAtAttribute
     * @param mixed $value
     * @return string
     */
    public function getCreatedAtAttribute($value)
    {
        return Carbon::parse($value)->format("y-m-d");
    }


    /**
     * Summary of getUpdatedAtAttribute
     * @param mixed $value
     * @return string
     */
    public function getUpdatedAtAttribute($value)
    {
        return Carbon::parse($value)->format("y-m-d");
    }

   

}
