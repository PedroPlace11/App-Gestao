<?php

namespace App\Listeners;

use App\Events\OrderCreated;
use Illuminate\Contracts\Queue\ShouldQueue;

class StoreOrderCreatedNotification implements ShouldQueue
{
    public function handle(OrderCreated $event): void
    {
        $order = $event->order->load('entity', 'user');

        if ($order->user) {
            $order->user->notify(
                new \App\Notifications\OrderCreatedNotification($order)
            );
        }
    }
}
