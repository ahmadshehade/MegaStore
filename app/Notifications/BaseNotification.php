<?php
namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

abstract class BaseNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Summary of via
     * @param mixed $notifiable
     * @return string[]
     */
    public function via($notifiable): array
    {
        return ['database'];
    }

    /**
     * Summary of basePayload
     * @param array $data
     * @return array{created_at: string|null, id: string}
     */
    protected function basePayload(array $data = []): array
    {
        return array_merge([
            'id'        => uniqid(),
            'created_at'=> now()->toISOString(),
        ], $data);
    }
}
