<?php

namespace Modules\PaymentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'order_id',
        'parent_invoice_id',
        'invoice_number',
        'tot_amount',
        'currency',
        'status',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'paid_at' => 'datetime',
        'tot_amount' => 'decimal:2',
    ];

    // =======================
    // RELATIONSHIPS
    // =======================

    public function order()
    {
        return $this->belongsTo(\Modules\OrderManagement\Models\Order::class);
    }

    public function parentInvoice()
    {
        return $this->belongsTo(self::class, 'parent_invoice_id');
    }

    public function childInvoices()
    {
        return $this->hasMany(self::class, 'parent_invoice_id');
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    public function ledgerEntries()
    {
        return $this->hasMany(LedgerEntry::class);
    }

    // =======================
    // HELPERS / SCOPES
    // =======================

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    public function isRevised(): bool
    {
        return $this->status === 'revised';
    }
}
