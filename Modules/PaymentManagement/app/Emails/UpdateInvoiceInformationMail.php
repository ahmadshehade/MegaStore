<?php

namespace Modules\PaymentManagement\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateInvoiceInformationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public  $invoice;
    /**
     * Create a new message instance.
     */
    public function __construct($inovice)
    {
        $this->invoice=$inovice;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject('Update Invoice To Your Order .')
        ->view('paymentmanagement::Emails.updateInvoice',['invoice'=> $this->invoice]);
    }
}
