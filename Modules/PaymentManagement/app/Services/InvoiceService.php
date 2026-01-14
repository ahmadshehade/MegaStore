<?php

namespace Modules\PaymentManagement\Services;

use App\Services\BaseService;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Modules\OrderManagement\Models\Order;
use Modules\PaymentManagement\Models\Invoice;

class InvoiceService extends BaseService
{
    protected $ledgerEntry;
    public function __construct(Invoice $model,LedgerEntryService $ledgerEntry)
    {
        parent::__construct($model);
        $this->ledgerEntry = $ledgerEntry;
    }

    /**
     * Get all invoices with caching
     */
    public function getAll(array $filters = []): iterable
    {
        $user     = Auth::user();
        $userId   = $user?->id ?? 'guest';
        $roles = $user
            ? implode('_', $user->getRoleNames()->toArray())
            : 'guest';

        $cacheKey = "invoices:{$userId}:{$roles}:" . md5(json_encode($filters));

        return Cache::tags(['invoices'])->remember(
            $cacheKey,
            now()->addMinute(),
            function () use ($filters) {
                return parent::getAll($filters)
                    ->load(['order', 'payments', 'refunds', 'ledgerEntries']);
            }
        );
    }

    /**
     * Get single invoice
     */
    public function get(Model $model): Model
    {
        return parent::get($model)
            ->load(['order', 'payments', 'refunds', 'ledgerEntries']);
    }

    /**
     * Get trashed invoices list
     */
    public function getTrashedInvoices(array $filters = [])
    {
        $user     = Auth::user();
        $userId   = $user?->id ?? 'guest';
        $roles = $user
            ? implode('_', $user->getRoleNames()->toArray())
            : 'guest';

        $cacheKey = "trashed_invoices:{$userId}:{$roles}:" . md5(json_encode($filters));

        return Cache::tags(['trashed_invoices'])->remember(
            $cacheKey,
            now()->addMinute(),
            function () use ($filters) {
                $query = Invoice::onlyTrashed()
                    ->with(['order', 'payments', 'refunds', 'ledgerEntries']);

                if (!empty($filters['order_id'])) {
                    $query->where('order_id', $filters['order_id']);
                }

                if (!empty($filters['invoice_number'])) {
                    $query->where('invoice_number', 'like', "%{$filters['invoice_number']}%");
                }

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                return $query->latest('deleted_at')->get();
            }
        );
    }

    /**
     * Get single trashed invoice
     */
    public function getTrashedInvoice(int $invoiceId): Invoice
    {
        return Invoice::onlyTrashed()
            ->with(['order', 'payments', 'refunds', 'ledgerEntries'])
            ->findOrFail($invoiceId);
    }

    /**
     * Create invoice
     */
    public function makeInvoice($order): Invoice
    {


        return parent::store([
            'order_id'       => $order->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'tot_amount'     => $order->tot_amount,
            'status'         => 'issued',
            'issued_at'      => now(),
        ]);
    }

    /**
     * Soft delete invoice
     */
    public function deleteInvoice(Order $order): bool
    {
        if (!$order->invoice) {
            throw new Exception('Invoice not found', 404);
        }

        $order->invoice->update([
            'status' => 'cancelled',
        ]);

        $order->invoice->delete();

        return true;
    }

    /**
     * Update invoice (revision)
     */
    public function updateInvoice($order): Invoice
    {


        $invoice = $order->invoice;

        if (!$invoice) {
            throw new Exception('Invoice not found', 404);
        }

        if ($invoice->status === 'paid') {
            throw new Exception('Cannot update a paid invoice', 422);
        }
        $payments=$invoice->payments()->get();
        $invoice->update([
            'status' => 'revised',
        ]);
        $newInvoice= parent::store([
            'order_id'           => $order->id,
            'parent_invoice_id'  => $invoice->id,
            'invoice_number'     => $this->generateInvoiceNumber(),
            'tot_amount'         => $order->tot_amount,
            'status'             => 'issued',
            'issued_at'          => now(),
        ]);
        foreach($payments  as $payment){
            $payment->update([
                'invoice_id'=>$newInvoice->id
            ]);
            $this->ledgerEntry->revisePaymentEntry($payment,$newInvoice->id);
        }
        $invoice->delete();

         return $newInvoice;

    }

    /**
     * Generate unique invoice number
     */
    protected function generateInvoiceNumber(): string
    {
        return 'INV-' . now()->format('YmdHis') . '-' . Str::upper(Str::random(4));
    }
}
