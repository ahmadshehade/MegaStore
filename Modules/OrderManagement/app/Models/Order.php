<?php

namespace Modules\OrderManagement\Models;

use App\Models\Base\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Modules\PaymentManagement\Models\Invoice;
use Modules\PaymentManagement\Models\Payment;
use Modules\PaymentManagement\Models\PaymentMethod;
use Modules\PaymentManagement\Models\Refund;
use Modules\OrderManagement\Models\OrderItem;
use Modules\OrderManagement\Models\OrderDiscountHistory;
use Modules\OrderManagement\Models\Discount;
use Modules\PaymentManagement\Models\LedgerEntry;

class Order extends BaseModel
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'status',
        'shipping_address',
        'tot_amount'
    ];

    /**
     * Summary of customer
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Order>
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * Summary of items
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderItem, Order>
     */
    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    /**
     * Summary of discounts
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany<Discount, Order, \Illuminate\Database\Eloquent\Relations\Pivot>
     */
    public function discounts()
    {
        return $this->belongsToMany(
            Discount::class,
            'order_discounts',
            'order_id',
            'discount_id'
        );
    }

    /**
     * Summary of histories
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<OrderDiscountHistory, Order>
     */
    public function histories()
    {
        return $this->hasMany(OrderDiscountHistory::class, 'order_id');
    }

    /**
     * Summary of invoice
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<Invoice, Order>
     */
    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'order_id');
    }


    /**
     * Summary of ledgerEntries
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LedgerEntry, Order>
     */
    public function ledgerEntries()
    {
          return $this->hasMany(LedgerEntry::class)
        ->whereHas('invoice', function ($q) {
            $q->whereNull('deleted_at');
        });
    }


}
