<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class InvoicePdfService
{
    public function generate(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        $invoice->load(['supplier', 'supplierOrder', 'user']);

        $company = Company::with('country')->first();
        $items = $this->buildItems($invoice);

        $subtotal = collect($items)->sum(fn ($item) => $item['unit_price'] * $item['quantity']);
        $taxAmount = max(0, (float) $invoice->total_value - $subtotal);

        $pdf = Pdf::loadView('pdf.invoice_supplier', [
            'invoice' => $invoice,
            'company' => $company,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    public function generateFromPayload(array $payload): \Barryvdh\DomPDF\PDF
    {
        $company = Company::with('country')->first();

        $supplier = (object) [
            'name' => (string) ($payload['supplier_name'] ?? 'Fornecedor'),
            'nif' => (string) ($payload['supplier_nif'] ?? '-'),
            'address' => (string) ($payload['supplier_address'] ?? '-'),
            'postal_code' => (string) ($payload['supplier_postal_code'] ?? ''),
            'city' => (string) ($payload['supplier_city'] ?? ''),
            'email' => (string) ($payload['supplier_email'] ?? ''),
        ];

        $issueDate = Carbon::parse((string) ($payload['issue_date'] ?? now()->toDateString()));
        $dueDate = Carbon::parse((string) ($payload['due_date'] ?? now()->toDateString()));

        $invoice = (object) [
            'number' => (string) ($payload['number'] ?? 'FAT-EXEMPLO'),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'supplier_id' => (int) ($payload['supplier_id'] ?? 0),
            'total_value' => (float) ($payload['total_value'] ?? 0),
            'status' => (string) ($payload['status'] ?? 'pending'),
            'supplier' => $supplier,
        ];

        $items = [[
            'name' => (string) ($payload['item_name'] ?? 'Fornecimento geral'),
            'description' => (string) ($payload['item_description'] ?? 'Fatura de exemplo gerada pelo sistema.'),
            'quantity' => 1,
            'unit_price' => (float) ($payload['total_value'] ?? 0),
            'total' => (float) ($payload['total_value'] ?? 0),
        ]];

        $subtotal = collect($items)->sum(fn ($item) => $item['unit_price'] * $item['quantity']);
        $taxAmount = max(0, (float) $invoice->total_value - $subtotal);

        $pdf = Pdf::loadView('pdf.invoice_supplier', [
            'invoice' => $invoice,
            'company' => $company,
            'items' => $items,
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    private function buildItems(Invoice $invoice): array
    {
        $sourceItems = $invoice->supplierOrder?->items;

        if (!is_array($sourceItems) || empty($sourceItems)) {
            return [[
                'name' => 'Fornecimento geral',
                'description' => 'Fatura sem linhas detalhadas da encomenda associada.',
                'quantity' => 1,
                'unit_price' => (float) $invoice->total_value,
                'total' => (float) $invoice->total_value,
            ]];
        }

        return array_map(function ($item) {
            $quantity = (float) ($item['quantity'] ?? 1);
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $total = $quantity * $unitPrice;

            return [
                'name' => (string) ($item['name'] ?? $item['description'] ?? 'Artigo'),
                'description' => (string) ($item['description'] ?? ''),
                'quantity' => $quantity,
                'unit_price' => $unitPrice,
                'total' => $total,
            ];
        }, $sourceItems);
    }
}
