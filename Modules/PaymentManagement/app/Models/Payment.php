<?php

namespace Modules\PaymentManagement\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\PaymentManagement\Models\Scopes\PaymentScope;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_id',
        'payment_method_id',
        'amount',
        'currency',
        'status',
        'payment_notes',
        'payment_date',
        'customer_id'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'datetime',
    ];

    // =======================
    // RELATIONSHIPS
    // =======================

    public function order()
    {
        return $this->belongsTo(\Modules\OrderManagement\Models\Order::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(\Modules\PaymentManagement\Models\PaymentMethod::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    /**
     * Summary of customer
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, Payment>
     */
    public  function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // =======================
    // HELPERS
    // =======================

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }

    public  static function booted()
    {
        static::addGlobalScope(new PaymentScope);
    }
}
