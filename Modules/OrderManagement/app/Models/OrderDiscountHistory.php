<?php

namespace Modules\OrderManagement\Models;

use App\Models\Base\BaseModel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class OrderDiscountHistory extends BaseModel
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'order_id',
        'discount_id',
        'discount_value',
        'discount_type',
        'total_amount_before_discount',
    ];

    protected $table='order_discount_history';


    /**
     * Summary of order
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Order, OrderDiscountHistory>
     */
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    /**
     * Summary of discount
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Discount, OrderDiscountHistory>
     */
    public function discount()
    {
        return $this->belongsTo(Discount::class, 'discount_id');
    }

    // protected static function newFactory(): OrderDiscountHistoryFactory
    // {
    //     // return OrderDiscountHistoryFactory::new();
    // }
}
