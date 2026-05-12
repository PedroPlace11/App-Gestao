<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Proposal;
use Barryvdh\DomPDF\Facade\Pdf;

class ProposalPdfService
{
    public function generate(Proposal $proposal): \Barryvdh\DomPDF\PDF
    {
        $proposal->load(['entity', 'user']);

        $company = Company::with('country')->first();

        // Build items array from JSON column (items stored as JSON in proposal)
        $items = $this->buildItems($proposal);

        $subtotal = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
        $tax_amount = $proposal->total_value - $subtotal;

        $pdf = Pdf::loadView('pdf.proposal', [
            'proposal'   => $proposal,
            'company'    => $company,
            'items'      => $items,
            'subtotal'   => $subtotal,
            'tax_amount' => $tax_amount,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    private function buildItems(Proposal $proposal): array
    {
        // Items stored as JSON in 'items' column; fall back to empty array
        if (isset($proposal->items) && is_array($proposal->items)) {
            return array_map(function ($item) {
                $qty        = $item['quantity'] ?? 1;
                $unit_price = $item['unit_price'] ?? 0;
                $tax_rate   = $item['tax_rate'] ?? 0;
                $subtotal   = $qty * $unit_price;
                $total      = $subtotal * (1 + $tax_rate / 100);

                return array_merge($item, [
                    'quantity'   => $qty,
                    'unit_price' => $unit_price,
                    'tax_rate'   => $tax_rate,
                    'total'      => $total,
                ]);
            }, $proposal->items);
        }

        return [];
    }
}
