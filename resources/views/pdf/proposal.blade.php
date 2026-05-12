<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Proposta {{ $proposal->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #333; }

        .header { display: flex; justify-content: space-between; margin-bottom: 30px; border-bottom: 2px solid #2563eb; padding-bottom: 15px; }
        .company-info h1 { font-size: 20px; color: #2563eb; font-weight: bold; }
        .company-info p { font-size: 10px; color: #555; margin-top: 2px; }
        .doc-info { text-align: right; }
        .doc-info h2 { font-size: 18px; color: #2563eb; text-transform: uppercase; }
        .doc-info .doc-number { font-size: 14px; font-weight: bold; color: #333; }
        .doc-info .doc-date { font-size: 10px; color: #666; }

        .client-section { display: flex; justify-content: space-between; margin-bottom: 25px; }
        .client-box, .validity-box { width: 48%; }
        .box-title { font-size: 10px; font-weight: bold; text-transform: uppercase; color: #2563eb; border-bottom: 1px solid #ddd; padding-bottom: 4px; margin-bottom: 8px; letter-spacing: 0.5px; }
        .client-box p, .validity-box p { font-size: 11px; line-height: 1.6; }
        .validity-box p span { font-weight: bold; }

        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead tr { background-color: #2563eb; color: #fff; }
        table thead th { padding: 8px 10px; text-align: left; font-size: 11px; }
        table thead th.right { text-align: right; }
        table tbody tr:nth-child(even) { background-color: #f8fafc; }
        table tbody td { padding: 7px 10px; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
        table tbody td.right { text-align: right; }

        .totals-section { display: flex; justify-content: flex-end; margin-bottom: 30px; }
        .totals-box { width: 280px; }
        .totals-row { display: flex; justify-content: space-between; padding: 5px 0; font-size: 11px; border-bottom: 1px solid #e5e7eb; }
        .totals-row.total { font-weight: bold; font-size: 13px; color: #2563eb; border-top: 2px solid #2563eb; border-bottom: none; padding-top: 8px; }

        .footer { margin-top: 40px; border-top: 1px solid #ddd; padding-top: 15px; }
        .footer p { font-size: 9px; color: #888; line-height: 1.5; }
        .footer-terms { margin-top: 8px; }
        .footer-terms p { font-size: 9px; color: #888; }

        .status-badge { display: inline-block; padding: 3px 8px; border-radius: 4px; font-size: 10px; font-weight: bold; text-transform: uppercase; }
        .status-draft { background-color: #fef3c7; color: #92400e; }
        .status-closed { background-color: #dcfce7; color: #166534; }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <div class="company-info">
            <h1>{{ $company->name ?? 'Empresa' }}</h1>
            @if($company)
            <p>NIF: {{ $company->nif ?? '' }}</p>
            <p>{{ $company->address ?? '' }}</p>
            <p>{{ $company->postal_code ?? '' }} {{ $company->city ?? '' }}</p>
            <p>{{ $company->phone ?? '' }} | {{ $company->email ?? '' }}</p>
            @endif
        </div>
        <div class="doc-info">
            <h2>Proposta</h2>
            <p class="doc-number">Nº {{ $proposal->number }}</p>
            <p class="doc-date">Data: {{ \Carbon\Carbon::parse($proposal->date)->format('d/m/Y') }}</p>
            <p>
                <span class="status-badge {{ $proposal->status === 'draft' ? 'status-draft' : 'status-closed' }}">
                    {{ $proposal->status === 'draft' ? 'Rascunho' : 'Fechada' }}
                </span>
            </p>
        </div>
    </div>

    <!-- Client & Validity -->
    <div class="client-section">
        <div class="client-box">
            <div class="box-title">Cliente</div>
            <p><strong>{{ $proposal->entity->name ?? 'N/A' }}</strong></p>
            <p>NIF: {{ $proposal->entity->nif ?? 'N/A' }}</p>
            <p>{{ $proposal->entity->address ?? '' }}</p>
            <p>{{ $proposal->entity->postal_code ?? '' }} {{ $proposal->entity->city ?? '' }}</p>
            <p>{{ $proposal->entity->email ?? '' }}</p>
        </div>
        <div class="validity-box">
            <div class="box-title">Informações</div>
            <p>Validade: <span>{{ \Carbon\Carbon::parse($proposal->validity)->format('d/m/Y') }}</span></p>
            <p>Criada por: <span>{{ $proposal->user->name ?? 'N/A' }}</span></p>
            @if($proposal->notes ?? false)
            <p>Notas: <span>{{ $proposal->notes }}</span></p>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <table>
        <thead>
            <tr>
                <th style="width:10%">Ref.</th>
                <th style="width:40%">Descrição</th>
                <th class="right" style="width:12%">Preço Unit.</th>
                <th class="right" style="width:10%">Qtd.</th>
                <th class="right" style="width:10%">IVA</th>
                <th class="right" style="width:18%">Total</th>
            </tr>
        </thead>
        <tbody>
            @forelse($items as $item)
            <tr>
                <td>{{ $item['reference'] ?? '-' }}</td>
                <td>{{ $item['name'] }}</td>
                <td class="right">{{ number_format($item['unit_price'], 2, ',', '.') }} €</td>
                <td class="right">{{ $item['quantity'] }}</td>
                <td class="right">{{ $item['tax_rate'] ?? 0 }}%</td>
                <td class="right">{{ number_format($item['total'], 2, ',', '.') }} €</td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="text-align:center; padding: 20px; color: #999;">Sem artigos</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Totals -->
    <div class="totals-section">
        <div class="totals-box">
            <div class="totals-row">
                <span>Subtotal:</span>
                <span>{{ number_format($subtotal, 2, ',', '.') }} €</span>
            </div>
            <div class="totals-row">
                <span>IVA:</span>
                <span>{{ number_format($tax_amount, 2, ',', '.') }} €</span>
            </div>
            <div class="totals-row total">
                <span>TOTAL:</span>
                <span>{{ number_format($proposal->total_value, 2, ',', '.') }} €</span>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div class="footer-terms">
            <p><strong>Termos e Condições:</strong></p>
            <p>Esta proposta é válida até {{ \Carbon\Carbon::parse($proposal->validity)->format('d/m/Y') }}. Após esta data, os preços e condições estão sujeitos a alteração.</p>
            <p>O pagamento deverá ser efetuado de acordo com as condições acordadas. Para dúvidas, entre em contacto connosco.</p>
        </div>
        <div style="margin-top:10px;">
            <p>Documento gerado em {{ now()->format('d/m/Y H:i') }} | {{ $company->name ?? '' }} @if($company) | NIF: {{ $company->nif ?? '' }} @endif</p>
        </div>
    </div>

</body>
</html>
