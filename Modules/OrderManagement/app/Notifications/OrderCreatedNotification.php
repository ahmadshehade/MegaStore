<?php

namespace Modules\OrderManagement\Notifications;

use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrderCreatedNotification extends BaseNotification
{
    public function __construct(
        protected $order
    ) {}

    public function toArray($notifiable): array
    {
        return $this->basePayload([
            'type'      => 'order_created',
            'order_id'  => $this->order->id,
            'status'    => $this->order->status,
            'message'   => 'New Order Creating ..',
        ]);
    }
}
