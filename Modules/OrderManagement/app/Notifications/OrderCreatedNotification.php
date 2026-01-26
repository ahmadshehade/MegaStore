<?php

namespace Modules\OrderManagement\Notifications;

use App\Notifications\BaseNotification;


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
