<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;
use Illuminate\Notifications\DatabaseNotification;

class StoreInvoicePaidNotification implements ShouldQueue
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice->load('supplier', 'user');

        // Notify the user who registered the invoice
        if ($invoice->user) {
            $invoice->user->notify(
                new \App\Notifications\InvoicePaidNotification($invoice)
            );
        }
    }
}
