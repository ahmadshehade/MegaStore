<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\OrderManagement\Models\Shipping;

class Day extends Model
{
    protected $fillable = [
        'name'
    ];


    /**
     * Summary of shippings
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Shipping, Day>
     */
    public function shippings(){
        return $this->hasMany(Shipping::class,'day_id');
    }

}
