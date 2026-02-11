<?php

namespace Modules\PaymentManagement\Models;

use App\Enum\UserRoles;
use App\Models\Base\BaseModel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Modules\PaymentManagement\Models\Scopes\PaymentScope;
use phpDocumentor\Reflection\Types\Null_;

class Payment extends BaseModel
{
    use HasFactory;

    /**
     * Summary of fillable
     * @var array
     */
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

    /**
     * Summary of casts
     * @var array
     */
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
    /**
     * Summary of invoice
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Invoice, Payment>
     */
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
    /**
     * Summary of paymentMethod
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<PaymentMethod, Payment>
     */
    public function paymentMethod()
    {
        return $this->belongsTo(\Modules\PaymentManagement\Models\PaymentMethod::class);
    }

    /**
     * Summary of refunds
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Refund, Payment>
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }
    /**
     * Summary of ledgerEntries
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LedgerEntry, Payment>
     */
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

    /**
     * Summary of isRefunded
     * @return bool
     */
    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }


    /**
     * Summary of scopeVisibleFor
     * @param mixed $query
     * @param mixed $user
     */
    public function  scopeVisibleFor($query, $user)
    {

        if ($user->hasRole(UserRoles::SuperAdmin->value)) {
            return $query;
        }

        if ($user->hasRole(UserRoles::Customer->value)) {
            return $query->whereHas('invoice.order', function ($q) use ($user) {
                $q->where('customer_id', $user->id);
            });
        }
        return $query->whereKey(Null);
    }
}
