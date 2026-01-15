<?php

namespace Modules\PaymentManagement\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Invoice extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * Summary of fillable
     * @var array
     */
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

    /**
     * Summary of casts
     * @var array
     */
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

    /**
     * Summary of parentInvoice
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<Invoice, Invoice>
     */
    public function parentInvoice()
    {
        return $this->belongsTo(self::class, 'parent_invoice_id');
    }

    /**
     * Summary of childInvoices
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Invoice, Invoice>
     */
    public function childInvoices()
    {
        return $this->hasMany(self::class, 'parent_invoice_id');
    }
    /**
     * Summary of payments
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Payment, Invoice>
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Summary of refunds
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<Refund, Invoice>
     */
    public function refunds()
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Summary of ledgerEntries
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<LedgerEntry, Invoice>
     */
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

    /**
     * Summary of isIssued
     * @return bool
     */
    public function isIssued(): bool
    {
        return $this->status === 'issued';
    }

    /**
     * Summary of isRevised
     * @return bool
     */
    public function isRevised(): bool
    {
        return $this->status === 'revised';
    }
}
