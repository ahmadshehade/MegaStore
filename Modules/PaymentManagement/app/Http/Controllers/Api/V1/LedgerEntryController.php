<?php

namespace Modules\PaymentManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PaymentManagement\Models\LedgerEntry;
use Modules\PaymentManagement\Services\LedgerEntryService;

class LedgerEntryController extends Controller
{
    protected LedgerEntryService $ledgerEntryService;

    public function __construct(LedgerEntryService $ledgerEntryService)
    {
        $this->ledgerEntryService = $ledgerEntryService;
    }

    /**
     * Display a listing of ledger entries
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'order_id',
            'invoice_id',
            'payment_id',
            'refund_id',
        ]);

        $ledgerEntries = $this->ledgerEntryService->getAll($filters);

        return $this->SuccessMessage(
            ['ledger_entries' => $ledgerEntries],
            'Successfully retrieved ledger entries.',
            200
        );
    }

    /**
     * Display a single ledger entry
     */
    public function show(LedgerEntry $ledgerEntry)
    {
        $data = $this->ledgerEntryService->get($ledgerEntry);

        return $this->SuccessMessage(
            ['ledger_entry' => $data],
            'Successfully retrieved ledger entry.',
            200
        );
    }

    /**
     * Display trashed ledger entries
     */
    public function getTrashedLedgerEntries(Request $request)
    {
        $filters = $request->only([
            'order_id',
            'invoice_id',
            'payment_id',
            'refund_id',
        ]);

        $ledgerEntries = $this->ledgerEntryService->getTrashedLedgerEntries($filters);

        return $this->SuccessMessage(
            ['ledger_entries' => $ledgerEntries],
            'Successfully retrieved trashed ledger entries.',
            200
        );
    }

    /**
     * Display a single trashed ledger entry
     */
    public function getTrashedLedgerEntry(int $ledgerEntryId)
    {
        $data = $this->ledgerEntryService->getTrashedLedgerEntry($ledgerEntryId);

        return $this->SuccessMessage(
            ['ledger_entry' => $data],
            'Successfully retrieved trashed ledger entry.',
            200
        );
    }
}
