<?php

namespace Modules\PaymentManagement\Notifications;

use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;


class MakePaymentNotification extends BaseNotification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected $payment) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via($notifiable): array
    {
        return ['database'];
    }



    /**
     * Get the array representation of the notification.
     */
    public function toArray($notifiable): array
    {
        return $this->basePayload([
            'payment_id' => $this->payment->id,
            'invoice_id' => $this->payment->invoice->id,
            'order_id' => $this->payment->invoice->order->id,
        ]);
    }
}
