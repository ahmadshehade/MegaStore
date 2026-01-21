<?php

namespace Modules\PaymentManagement\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class DeleteInvoiceInformation extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public  $invoice_id;
    public $status;
    public $order_id;

    /**
     * Create a new message instance.
     */
    public function __construct($invoice_id, $status, $order_id)
    {
        $this->invoice_id = $invoice_id;
        $this->status = $status;
        $this->order_id = $order_id;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Delete Invoice ')
            ->view('paymentmanagement::Emails.deleleteInvoice', [
                'inovice_id' => $this->invoice_id,
                'status' => $this->status,
                'order_id' => $this->order_id,
            ]);
    }
}
