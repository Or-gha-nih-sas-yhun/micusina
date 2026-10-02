<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class RiderAssignedToOrder extends Notification
{
    use Queueable;

    public function __construct(private readonly Order $order) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'rider_assignment',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'You have been assigned to delivery #'.$this->order->order_number.'.',
        ];
    }
}
