<?php

namespace Modules\PaymentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Refund extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'invoice_id',
        'payment_id',
        'amount',
        'currency',
        'refund_date',
        'reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'refund_date' => 'datetime',
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

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }
}
