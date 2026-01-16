<?php

namespace Modules\OrderManagement\Notifications;

use App\Notifications\BaseNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OrderDeleteNotification extends BaseNotification
{
    /**
     * Summary of __construct
     * @param int $orderId
     * @param string $status
     */
    public function __construct(
        protected int $orderId,
        protected string $status
    ) {}

    /**
     * Summary of toArray
     * @param mixed $notifiable
     * @return array{created_at: string|null, id: string}
     */
    public function toArray($notifiable): array
    {
        return $this->basePayload([
            'type'      => 'order_deleted',
            'order_id'  => $this->orderId,
            'status'    => $this->status,
            'message'   => 'Order has been deleted',
        ]);
    }
}
