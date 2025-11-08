<?php

namespace Modules\OrderManagement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\ProductManagment\Models\Product;

// use Modules\OrderManagement\Database\Factories\OrderItemFactory;

class OrderItem extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['order_id','product_id','price','quantity','total'];


    /**
     * Summary of order
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, OrderItem>
     */
    public function order(){
        return $this->belongsTo(Order::class,'order_id');
    }

    /**
     * Summary of product
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Product, OrderItem>
     */
    public function product(){
        return $this->belongsTo(Product::class,'product_id');
    }

    // protected static function newFactory(): OrderItemFactory
    // {
    //     // return OrderItemFactory::new();
    // }
}
