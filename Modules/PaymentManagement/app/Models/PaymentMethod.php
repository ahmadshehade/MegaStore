<?php

namespace Modules\PaymentManagement\Models;

use App\Models\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\OrderManagement\Models\Order;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// use Modules\PaymentManagement\Database\Factories\PaymentMethodFactory;

class PaymentMethod extends BaseModel implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name','description','code','is_active','config'];
    /**
     * Summary of table
     * @var string
     */
    protected $table='payment_methods';

    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value){
        return ucwords($value);
    }

    /**
     * Summary of getDescriptionAttribute
     * @param mixed $value
     * @return string
     */
    public function getDescriptionAttribute($value){
        return ucwords($value);
    }

    /**
     * Summary of setNameAttribute
     * @param mixed $value
     * @return void
     */
    public function setNameAttribute($value){
        $this->attributes['name'] = strtolower($value);
    }

    /**
     * Summary of setDescriptionAttribute
     * @param mixed $value
     * @return void
     */
    public  function setDescriptionAttribute($value){
        $this->attributes['description'] = strtolower($value);
    }

    /**
     * Summary of image
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<Media, PaymentMethod>
     */
    public  function image(){
        return $this->morphOne(Media::class,'model');
    }

    /**
     * Summary of Orders
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Order, PaymentMethod>
     */
    public function Orders(){
        return $this->hasMany(Order::class,'payment_method_id');
    }





    // protected static function newFactory(): PaymentMethodFactory
    // {
    //     // return PaymentMethodFactory::new();
    // }
}
