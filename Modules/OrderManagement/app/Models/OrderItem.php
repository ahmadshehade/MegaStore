<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagement\Models\Product;
use Modules\ProductManagement\Models\ProductVariant;

// use Modules\OrderManagement\Database\Factories\OrderItemFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'product_variant_id',
        'unit_price',
        'quantity',
        'subtotal',
        'meta'
    ];

    /**
     * Summary of productVariant
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<ProductVariant, OrderItem>
     */
    public function productVariant(){
        return $this->belongsTo(ProductVariant::class,'product_variant_id');
    }

    /**
     * Summary of order
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, OrderItem>
     */
    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }

    // protected static function newFactory(): OrderItemFactory
    // {
    //     // return OrderItemFactory::new();
    // }
}
