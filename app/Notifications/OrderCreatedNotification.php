<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Order $order)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'       => 'order_created',
            'title'      => "Nova encomenda: {$this->order->number}",
            'message'    => "A encomenda {$this->order->number} para o cliente {$this->order->entity?->name} foi criada.",
            'order_id'   => $this->order->id,
            'order_number' => $this->order->number,
            'total_value' => $this->order->total_value,
        ];
    }
}
