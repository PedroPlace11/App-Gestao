<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class InvoicePaidNotification extends Notification
{
    use Queueable;

    public function __construct(public readonly Invoice $invoice)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toDatabase(object $notifiable): array
    {
        return [
            'type'    => 'invoice_paid',
            'title'   => "Fatura paga: {$this->invoice->number}",
            'message' => "A fatura {$this->invoice->number} do fornecedor {$this->invoice->supplier?->name} foi marcada como paga.",
            'invoice_id' => $this->invoice->id,
            'invoice_number' => $this->invoice->number,
            'total_value' => $this->invoice->total_value,
        ];
    }
}
