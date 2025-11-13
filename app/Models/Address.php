<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Modules\OrderManagement\Models\Order;

class Address extends BaseModel
{

    protected $fillable = [
        'user_id',
        'type',
        'country',
        'state',
        'city',
        'address',
        'postal_code',
        'phone',
        'is_default'
    ];


    /**
     * Summary of user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Address>
     */
    public function user(){
        return $this->belongsTo(User::class,'user_id');
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    public function orders(){
        return $this->hasMany(Order::class,'address_id');
    }
}
