<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fatura</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            max-width: 150px;
            max-height: 80px;
        }
        .header .company-info {
            text-align: right;
        }
        .content {
            margin: 20px;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 20px;
            text-align: right;
        }
        .footer {
            text-align: center;
            font-size: 10px;
            margin-top: 20px;
        }
        .terms {
            margin-top: 20px;
            font-size: 10px;
        }
    </style>
</head>
<body>
    @php
        $companyName = trim((string) ($company->name ?? ''));
        $companyAddress = trim((string) ($company->address ?? ''));
        $companyPostalCode = trim((string) ($company->postal_code ?? ''));
        $companyCity = trim((string) ($company->city ?? ''));
        $companyTaxId = trim((string) ($company->tax_id ?? ''));

        $documentType = isset($proposal) ? 'Proposta' : (isset($order) ? 'Encomenda' : 'Fatura');
        $documentNumber = $proposal->number ?? $order->number ?? $invoice->number ?? null;
        $issueDate = $proposal->date ?? $proposal->issue_date ?? $order->date ?? $order->issue_date ?? $invoice->issue_date ?? null;
        $dueDate = $proposal->valid_until ?? $proposal->due_date ?? $order->due_date ?? $invoice->due_date ?? null;
        $totalWithTax = $proposal->total_value ?? $order->total_value ?? $invoice->total_value ?? 0;

        $logoSrc = null;
        $logoValue = trim((string) ($company->logo ?? ''));

        if ($logoValue !== '') {
            if (str_starts_with($logoValue, 'http://') || str_starts_with($logoValue, 'https://')) {
                $logoSrc = $logoValue;
            } else {
                $normalizedPath = str_starts_with($logoValue, '/storage/')
                    ? public_path('storage/' . ltrim(substr($logoValue, strlen('/storage/')), '/'))
                    : public_path(ltrim($logoValue, '/'));

                if (is_file($normalizedPath)) {
                    $logoSrc = $normalizedPath;
                }
            }
        }
    @endphp

    <div class="header">
        <div>
            @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="Logo da Empresa">
            @endif
        </div>
        <div class="company-info">
            <h1>{{ $companyName !== '' ? $companyName : 'Nome da Empresa' }}</h1>
            <p>{{ $companyAddress !== '' ? $companyAddress : 'Endereço não disponível' }}</p>
            <p>{{ trim($companyPostalCode . ' ' . $companyCity) }}</p>
            <p>Contribuinte: {{ $companyTaxId !== '' ? $companyTaxId : 'N/A' }}</p>
        </div>
    </div>

    <div class="content">
        <h2>Detalhes do Documento</h2>
        <p><strong>Número da {{ $documentType }}:</strong> {{ $documentNumber ?? 'N/A' }}</p>
        <p><strong>Data de Emissão:</strong> {{ $issueDate ?? 'N/A' }}</p>
        <p><strong>Data de Vencimento:</strong> {{ $dueDate ?? 'N/A' }}</p>

        <h3>Itens</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Descrição</th>
                    <th>Quantidade</th>
                    <th>Preço Unitário</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @if (!empty($items) && is_iterable($items))
                @foreach ($items as $item)
                <tr>
                    <td>{{ $item['description'] ?? $item['name'] ?? 'Item' }}</td>
                    <td>{{ $item['quantity'] }}</td>
                    <td>{{ number_format($item['unit_price'], 2, ',', '.') }} €</td>
                    <td>{{ number_format($item['unit_price'] * $item['quantity'], 2, ',', '.') }} €</td>
                </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="4" style="text-align: center;">Nenhum item disponível.</td>
                </tr>
            @endif
        </table>

        <div class="summary">
            <p><strong>Subtotal:</strong> {{ number_format($subtotal, 2, ',', '.') }} €</p>
            <p><strong>Desconto Linha:</strong> 0,00 €</p>
            <p><strong>Desconto Geral:</strong> 0,00 €</p>
            <p><strong>Total sem IVA:</strong> {{ number_format($subtotal, 2, ',', '.') }} €</p>
            <p><strong>IVA:</strong> {{ number_format($tax_amount, 2, ',', '.') }} €</p>
            <p><strong>Total com IVA:</strong> {{ number_format($totalWithTax, 2, ',', '.') }} €</p>
        </div>

        <div class="terms">
            <h3>Termos e Condições</h3>
            <p><strong>Prazo Entrega:</strong> 30 DIAS</p>
            <p><strong>Condições de Pagamento:</strong></p>
            <ul>
                <li>Adjudicação: 50%</li>
                <li>Conclusão: 50%</li>
            </ul>
            <p><strong>Validade até:</strong> {{ $proposal->valid_until ?? $dueDate ?? 'N/A' }}</p>
            <p>*Valor sem IVA incluído</p>
        </div>

        <div class="payment-info">
            <h3>Informações de Pagamento</h3>
            <p>IBAN PT50 0033 0000 0017 1871879 05 (MillenniumBcp)</p>
            <p>IBAN PT50 0269 0660 002096300112 (Bankinter)</p>
        </div>

        <div class="footer">
            <p>Este documento não serve de fatura</p>
            <p>Entidade: 11 473</p>
            <p>Referência: 969 006 950</p>
            <p>Valor: 27,68 €</p>
        </div>
    </div>
</body>
</html>
