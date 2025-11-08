<?php

namespace Modules\OrderManagement\Models;

use App\Models\BaseModel;
use App\Models\Day;
use Database\Factories\ShippingFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

// use Modules\OrderManagement\Database\Factories\ShippingFactory;

class Shipping extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'cost', 'is_active', 'day_id'];

    /**
     * Summary of casts
     * @var array
     */
    protected $casts = [
        'name' => 'string',
        'is_active' => 'boolean',
        'day_id' => 'integer'
    ];

    public function day()
    {
        return $this->belongsTo(Day::class, 'day_id');
    }


    /**
     * Summary of image
     * @return \Illuminate\Database\Eloquent\Relations\MorphOne<Media, Shipping>
     */
    public function image()
    {
        return $this->morphOne(Media::class, 'model');
    }


    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return Str::upper($value);
    }


    /**
     * Summary of setNameAttribute
     * @param mixed $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = Str::upper($value);
    }



    /**
     * Summary of newFactory
     * @return ShippingFactory
     */
    protected static function newFactory(){
        return ShippingFactory::new();
    }


    public function orders(){
        return $this->hasMany(Order::class,'shipping_id');
    }
}
