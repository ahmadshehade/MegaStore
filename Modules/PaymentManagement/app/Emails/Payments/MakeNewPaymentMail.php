<?php

namespace Modules\PaymentManagement\Emails\Payments;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class MakeNewPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $payment;
    /**
     * Create a new message instance.
     */
    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        try {
            return  $this->subject('Make New Payment')
                ->view('paymentmanagement::Emails.Payments.makepayment')
                ->with(['payment'=>$this->payment]);
        } catch (\Exception $e) {
            Log::error('Fail To Send  Paymet Mail .' . $e->getMessage());
            throw $e;
        }
    }
}
