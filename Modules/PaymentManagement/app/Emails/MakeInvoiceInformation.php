<?php

namespace Modules\PaymentManagement\Emails;

use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\PaymentManagement\Models\Invoice;

class MakeInvoiceInformation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }


    /**
     * Summary of build
     * 
     */
    public function build()
    {
        try {

            return $this
                ->subject('New Invoice Generated for Your Order')
                ->view('paymentmanagement::Emails.makeInvoice', [
                    'invoice' => $this->invoice,
                ]);

        } catch (Exception $e) {
            Log::error("Fail Send mail To Make Invoice :" . $e->getMessage());
        }
    }
}
