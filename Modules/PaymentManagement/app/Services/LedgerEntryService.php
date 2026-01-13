<?php

namespace Modules\PaymentManagement\Services;

use App\Services\BaseService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use LogicException;
use Modules\PaymentManagement\Models\LedgerEntry;

class LedgerEntryService extends BaseService
{
    public function __construct(LedgerEntry $model)
    {
        parent::__construct($model);
    }
    /**
     * Get all ledger entries with filters
     *
     * @param array $filters
     * @return iterable
     */
    public function getAll(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user
            ? $user->id . '_' . implode('_', $user->roles()->pluck('name')->toArray())
            : 'guest';

        $cacheKey = 'ledger_entries_' . $userKey . '_' . md5(json_encode($filters));

        return Cache::tags(['LedgerEntries'])->remember(
            $cacheKey,
            now()->addMinute(),
            function () use ($filters) {
                return parent::getAll($filters)
                    ->load(['order', 'invoice', 'payment', 'refund']);
            }
        );
    }

    /**
     * Get single ledger entry
     *
     * @param Model $model
     * @return Model
     */
    public function get(Model $model): Model
    {
        return parent::get($model)
            ->load(['order', 'invoice', 'payment', 'refund']);
    }

    /**
     * Get trashed ledger entries
     *
     * @param array $filters
     * @return iterable
     */
    public function getTrashedLedgerEntries(array $filters = []): iterable
    {
        $user = Auth::user();
        $userKey = $user
            ? $user->id . '_' . implode('_', $user->roles()->pluck('name')->toArray())
            : 'guest';

        $cacheKey = 'trashed_ledger_entries_' . $userKey . '_' . md5(json_encode($filters));

        return Cache::tags(['T_LedgerEntries'])->remember(
            $cacheKey,
            now()->addMinute(),
            function () use ($filters) {
                $query = LedgerEntry::onlyTrashed();

                if (!empty($filters['order_id'])) {
                    $query->where('order_id', $filters['order_id']);
                }

                if (!empty($filters['invoice_id'])) {
                    $query->where('invoice_id', $filters['invoice_id']);
                }

                if (!empty($filters['payment_id'])) {
                    $query->where('payment_id', $filters['payment_id']);
                }

                if (!empty($filters['refund_id'])) {
                    $query->where('refund_id', $filters['refund_id']);
                }

                $entries = $query->get();

                if ($entries->isEmpty()) {
                    throw new HttpResponseException(
                        response()->json(['message' => 'No trashed ledger entries found.'], 404)
                    );
                }

                return $entries->load(['order', 'invoice', 'payment', 'refund']);
            }
        );
    }

    /**
     * Get single trashed ledger entry
     *
     * @param int $ledgerId
     * @return LedgerEntry
     */
    public function getTrashedLedgerEntry(int $ledgerId): LedgerEntry
    {
        $ledgerEntry = LedgerEntry::onlyTrashed()->findOrFail($ledgerId);

        return $ledgerEntry->load(['order', 'invoice', 'payment', 'refund']);
    }


    /**
     * Summary of addEntryToInvocie
     * @param mixed $invoice
     * @return LedgerEntry
     */
    public function createInvoiceEntry($invoice): LedgerEntry
    {
        return LedgerEntry::create([
            'order_id'   => $invoice->order_id,
            'invoice_id' => $invoice->id,
            'entry_type' => 'invoice',
            'debit'      => $invoice->tot_amount,
            'credit'     => 0,
            'description' => 'Invoice issued',
        ]);
    }


    /**
     * Summary of updateEntryInvoice
     * @param mixed $invoice
     * @return LedgerEntry
     */
    public function reviseInvoiceEntry($invoice): LedgerEntry
    {
        $oldEntry = LedgerEntry::where('order_id', $invoice->order->id)
            ->where('entry_type', 'invoice')
            ->latest()
            ->firstOrFail();


        LedgerEntry::create([
            'order_id'   => $oldEntry->order_id,
            'invoice_id' => $oldEntry->invoice_id,
            'entry_type' => 'invoice_reversal',
            'debit'      => 0,
            'credit'     => $oldEntry->debit,
            'description' => 'Invoice reversed',
        ]);


        return LedgerEntry::create([
            'order_id'   => $invoice->order_id,
            'invoice_id' => $invoice->id,
            'entry_type' => 'invoice',
            'debit'      => $invoice->tot_amount,
            'credit'     => 0,
            'description' => 'Invoice revised',
        ]);
    }



    public function createPaymentEntry($payment): LedgerEntry{


     return LedgerEntry::create([
            'order_id'   => $payment->invoice->order->id,
            'invoice_id' => $payment->invoice->id,
            'entry_type' => 'payment',
            'debit'      => 0.00,
            'credit'     => $payment->amount,
            'description' => 'Payment issued',
        ]);

    }
}
