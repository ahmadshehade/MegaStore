<?php

namespace Modules\ProductManagement\Models;

use App\Models\Base\BaseModel;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

// use Modules\ProductManagement\Database\Factories\CategoryFactory;

class Category extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['name', 'slug', 'descriptions', 'parent_id'];



    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value){
        return  Str::ucfirst($value);
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
     * Summary of getDescriptionsAttribute
     * @param mixed $value
     * @return string
     */
    public function getDescriptionsAttribute($value){
        return ucwords($value);
    }

    /**
     * Summary of setDescriptionsAttribute
     * @param mixed $value
     * @return void
     */
    public function setDescriptionsAttribute($value){
        $this->attributes['descriptions'] = strtolower($value);
    }

    /**
     * Summary of childrens
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Category, Category>
     */
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    /**
     * Summary of parent
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Category, Category>
     */
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }


    /**
     * Summary of products
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Product, Category>
     */
    public function products()
    {
        return $this->hasMany(Product::class, 'category_id');
    }


    /**
     * Summary of newFactory
     * @return CategoryFactory
     */
    protected static function newFactory(): CategoryFactory
    {
          return new CategoryFactory();
    }
}
