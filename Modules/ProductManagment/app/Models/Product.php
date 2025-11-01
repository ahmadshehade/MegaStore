<?php

namespace Modules\ProductManagment\Models;

use App\Models\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use Modules\ProductManagment\Database\Factories\ProductFactory;

class Product extends BaseModel implements HasMedia
{
    use HasFactory,InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name','description','price','stock','category_id','seller_id'];

    protected $table='products';



    /**
     * Summary of category
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, Product>
     */
    public function category(){
        return $this->belongsTo(Category::class,'category_id');
    }


    /**
     * Summary of seller
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Product>
     */
    public function seller(){
        return $this->belongsTo(User::class,'seller_id');
    }


    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public  function getNameAttribute($value){
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
    public function  setNameAttribute($value){
        $this->attributes['name'] = strtolower($value);
    }

    /**
     * Summary of setDescriptionAttribute
     * @param mixed $value
     * @return void
     */
    public function setDescriptionAttribute($value){
        $this->attributes['description'] = strtolower($value);
    }







    
    // protected static function newFactory(): ProductFactory
    // {
    //     // return ProductFactory::new();
    // }
}
