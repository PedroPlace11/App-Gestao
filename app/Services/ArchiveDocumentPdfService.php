<?php

namespace App\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class ArchiveDocumentPdfService
{
    public function generate(array $document): \Barryvdh\DomPDF\PDF
    {
        $type = (string) ($document['type'] ?? 'Documento');

        $viewName = match ($type) {
            'Fatura' => 'pdf.archive_invoice',
            'Contrato' => 'pdf.contract',
            'Proposta' => 'pdf.proposal',
            'Comprovativo' => 'pdf.proof_of_payment',
            default => 'pdf.contract',
        };

        $pdf = Pdf::loadView($viewName, ['document' => $document]);
        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
}
