<?php

namespace Modules\ProductManagement\Models;

use App\Models\Base\BaseModel;
use App\Models\User;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;
use Modules\OrderManagement\Models\OrderItem;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int,string>
     */
    protected $fillable = [
        'category_id',
        'sku',
        'name',
        'slug',
        'short_description',
        'description',
        'brand',
        'status',
        'is_featured',
        'meta',
        'created_by',
    ];

    /**
     * Summary of casts
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
        'is_featured' => 'boolean',
    ];


    /**
     * Summary of getNameAttribute
     * @param mixed $value
     * @return string
     */
    public function getNameAttribute($value)
    {
        return  Str::ucfirst($value);
    }

    /**
     * Summary of getShortDescriptionAttribute
     * @param mixed $value
     * @return string
     */
    public function getShortDescriptionAttribute($value){
        return Str::ucwords($value);
    }


    /**
     * Summary of setShortDescriptionAttribute
     * @param mixed $value
     * @return void
     */
    public function setShortDescriptionAttribute($value){
        $this->attributes['short_description'] = strtolower($value);
    }

    /**
     * Summary of getDescriptionAttribute
     * @param mixed $value
     * @return string
     */
    public function getDescriptionAttribute($value){
        return Str::ucwords($value);
    }

    /**
     * Summary of setDescriptionAttribute
     * @param mixed $value
     * @return void
     */
    public function setDescriptionAttribute($value){
        $this->attributes['description'] = strtolower($value);
    }

    /**
     * Summary of setNameAttribute
     * @param mixed $value
     * @return void
     */
    public function  setNameAttribute($value)
    {
        $this->attributes['name'] = strtolower($value);
    }
    /**
     * Creator / maker relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Category relationship.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    /**
     * Summary of productVariants
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<ProductVariant, Product>
     */
    public function productVariants()
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    /**
     * Summary of newFactory
     * @return ProductFactory
     */
    protected static function newFactory()
    {
        return new ProductFactory();
    }


}
