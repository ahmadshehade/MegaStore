<?php

namespace Modules\PaymentManagement\Models;

use App\Enum\EntryType;
use App\Enum\UserRoles;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LedgerEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_id',
        'payment_id',
        'refund_id',
        'entry_type',
        'debit',
        'credit',
        'description',
        'entry_date',
        'customer_id'
    ];

    protected $casts = [
        'debit' => 'decimal:2',
        'credit' => 'decimal:2',
        'entry_date' => 'datetime',
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

    public function payment()
    {
        return $this->belongsTo(Payment::class);
    }

    public function refund()
    {
        return $this->belongsTo(Refund::class);
    }

    /**
     * Summary of customer
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<User, LedgerEntry>
     */
    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }


    /**
     * Summary of scopeWithoutReversals
     * @param mixed $query
     */
    public  function scopeWithoutReversals($query)
    {
        return $query->whereNotIn('entry_type', [
            EntryType::InvoiceReversal->value,
            EntryType::PaymentReversal->value,
        ]);
    }

    /**
     * Summary of scopeWithReversals
     * @param mixed $query
     */
    public function scopeWithReversals($query)
    {
        return $query;
    }

    /**
     * Summary of scopeVisibleFor
     * @param mixed $query
     * @param User $user
     */
    public function scopeVisibleFor($query, User $user)
    {
       if($user->hasRole(UserRoles::SuperAdmin->value)){
           return $query->withReversals();
       }
       if($user->hasRole(UserRoles::Customer->value)){
         return $query->withoutReversals();
       }
    }
}
