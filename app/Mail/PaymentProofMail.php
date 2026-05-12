<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentProofMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly Invoice $invoice)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Comprovativo de Pagamento - Fatura {$this->invoice->number}",
        );
    }

    public function content(): Content
    {
        $company = Company::first();

        return new Content(
            view: 'emails.payment_proof',
            with: [
                'invoice'       => $this->invoice->load('supplier'),
                'company_name'  => $company?->name,
                'company_email' => $company?->email,
                'paid_at'       => $this->invoice->updated_at,
            ],
        );
    }

    public function attachments(): array
    {
        $company = Company::first();
        $invoice = $this->invoice->load('supplier');

        $pdf = Pdf::loadView('emails.payment_proof', [
            'invoice'       => $invoice,
            'company_name'  => $company?->name,
            'company_email' => $company?->email,
            'paid_at'       => $invoice->updated_at,
        ])->setPaper('A4', 'portrait');

        return [
            Attachment::fromData(
                fn () => $pdf->output(),
                "comprovativo_{$invoice->number}.pdf"
            )->withMime('application/pdf'),
        ];
    }
}
