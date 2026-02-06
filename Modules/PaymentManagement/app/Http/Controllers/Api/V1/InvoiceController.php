<?php

namespace Modules\PaymentManagement\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\PaymentManagement\Models\Invoice;
use Modules\PaymentManagement\Services\InvoiceService;

class InvoiceController extends Controller
{

    /**
     * Summary of invoice
     * @var
     */
    protected $invoice;
    public function __construct(InvoiceService $invoice)
    {
        $this->invoice = $invoice;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $filters = $request->only(['status', 'order_id', 'invoice_number']);
        $invoices = $this->invoice->getAll($filters);
        return $this->SuccessMessage(['invoices' => $invoices], 'Successfully Get All  Active Invoice.', 200);
    }


    public function show(Invoice $invoice)
    {
        $data = $this->invoice->get($invoice);
        return $this->SuccessMessage(['Invoice' => $invoice], 'Successfully  Get The Invoice .', 200);
    }

    /**
     * Summary of getTrashedInvoice
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrashedInvoices(Request  $request)
    {
        $filters = $request->only(['status', 'order_id', 'invoice_number']);
        $invoices = $this->invoice->getTrashedInvoices($filters);
        return $this->SuccessMessage(['invoices' => $invoices], 'Successfully Get All  Trashed Invoice.', 200);
    }

    /**
     * Summary of getTrashedInvoice
     * @param Invoice $invoice
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTrashedInvoice(Invoice $invoice)
    {

        $data = $this->invoice->getTrashedInvoice($invoice);
        return $this->SuccessMessage(['invoices' => $data], 'Successfully   Trashed Invoice.', 200);
    }
}
