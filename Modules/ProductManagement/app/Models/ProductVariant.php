<?php

namespace Modules\ProductManagement\Models;

use App\Models\Base\BaseModel;
use Database\Factories\ProductVaraintFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Illuminate\Database\Eloquent\Factories\Factory\ProductVariantsFactory;
use Modules\OrderManagement\Models\OrderItem;

class ProductVariant extends BaseModel implements HasMedia
{
    use HasFactory, InteractsWithMedia;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'product_id',
        'sku',
        'price',
        'compare_price',
        'stock_quantity',
        'low_stock_threshold',
        'weight',
        'is_active',
    ];


    /**
     * Summary of casts
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'is_active' => 'boolean',
    ];



    /**
     * Summary of product
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, ProductVariant>
     */
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }



    /**
     * Summary of newFactory
     * @return ProductVaraintFactory
     */
    protected static function newFactory()
    {
        return  new ProductVaraintFactory();
    }
    /**
     * Summary of attributes
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Attribute, ProductVariant, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function attributes()
    {
        return $this->belongsToMany(
            Attribute::class,
            'variant_values',
            'product_variant_id',
            'attribute_id'
        );
    }

        /**
     * Summary of orderItems
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderItem, Product>
     */
    public function orderItems(){
        return $this->hasMany(OrderItem::class,'product_variant_id');
    }
}
