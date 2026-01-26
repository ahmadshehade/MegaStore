<?php

namespace Modules\OrderManagement\Emails;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class OverPaymentMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $order;

    public $overPayment;
    /**
     * Create a new message instance.
     */
    public function __construct($order, $overPayment)
    {
        $this->order = $order;
        $this->overPayment = $overPayment;
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        return $this->subject("OverPayment To Your Order After Discount")
            ->view('ordermanagement::Emails.addOverPayment')
            ->with(['order' => $this->order, 'overPayment' => $this->overPayment]);
    }
}
