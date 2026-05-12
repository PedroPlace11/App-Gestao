<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Mail\PaymentProofMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendPaymentProofEmail implements ShouldQueue
{
    public string $queue = 'emails';

    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice->load('supplier');

        if ($invoice->supplier?->email) {
            Mail::to($invoice->supplier->email)
                ->send(new PaymentProofMail($invoice));
        }
    }
}
