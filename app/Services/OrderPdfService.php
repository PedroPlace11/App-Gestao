<?php

namespace App\Services;

use App\Models\Company;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderPdfService
{
    public function generate(Order $order): \Barryvdh\DomPDF\PDF
    {
        $order->load(['entity', 'user']);

        $company = Company::with('country')->first();

        $items = $this->buildItems($order);

        $subtotal = collect($items)->sum(fn($i) => $i['unit_price'] * $i['quantity']);
        $tax_amount = $order->total_value - $subtotal;

        $pdf = Pdf::loadView('pdf.order', [
            'order'      => $order,
            'company'    => $company,
            'items'      => $items,
            'subtotal'   => $subtotal,
            'tax_amount' => $tax_amount,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }

    private function buildItems(Order $order): array
    {
        if (isset($order->items) && is_array($order->items)) {
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
            }, $order->items);
        }

        return [];
    }
}
