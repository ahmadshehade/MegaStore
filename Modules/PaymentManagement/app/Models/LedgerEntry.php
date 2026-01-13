<?php

namespace Modules\PaymentManagement\Models;

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
}
