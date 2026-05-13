<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Proposta - {{ $proposal->number ?? $document['name'] ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
        .page { padding: 30px; }
        .header { border-bottom: 3px solid #7c3aed; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #7c3aed; font-size: 24px; font-weight: 700; }
        .section { margin-bottom: 20px; }
        .section-title { background: #7c3aed; color: #fff; padding: 6px 8px; font-size: 10px; font-weight: 700; margin-bottom: 8px; }
        .section-content { padding: 10px; border: 1px solid #e9d5ff; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .label { font-weight: 600; }
        .value { color: #4b5563; }
        .proposal-box { background: #f5f3ff; border-left: 4px solid #7c3aed; padding: 10px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; margin-top: 10px; }
        table thead tr { background-color: #7c3aed; color: #fff; }
        table thead th { padding: 8px 10px; text-align: left; font-size: 10px; }
        table tbody tr:nth-child(even) { background-color: #f5f3ff; }
        table tbody td { padding: 7px 10px; font-size: 10px; border-bottom: 1px solid #e9d5ff; }
        .footer { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 15px; text-align: center; color: #666; font-size: 9px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>PROPOSTA COMERCIAL</h1>
    </div>

    <div class="section">
        <div class="section-title">INFORMAÇÕES DA PROPOSTA</div>
        <div class="section-content">
            <div class="detail-row">
                <span class="label">Documento:</span>
                <span class="value">{{ $proposal->number ?? $document['name'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Entidade:</span>
                <span class="value">{{ $proposal->client?->name ?? $document['entity'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Data de Emissão:</span>
                <span class="value">{{ $proposal->issued_date?->format('d/m/Y') ?? $document['date'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value">{{ $proposal->status ?? $document['status'] ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">ESCOPO DA PROPOSTA</div>
        <div class="section-content">
            <div class="proposal-box">
                <p><strong>Objetivo:</strong> Esta proposta tem como objetivo apresentar uma solução sob medida para as necessidades do cliente.</p>
            </div>
            <p style="margin-top: 10px;">A nossa proposta inclui:</p>
            <ul style="margin-left: 20px; margin-top: 8px;">
                <li>Análise detalhada dos requisitos</li>
                <li>Solução personalizada</li>
                <li>Implementação e suporte</li>
                <li>Formação e documentação</li>
            </ul>
        </div>
    </div>

    @if ($proposal?->items ?? false)
    <div class="section">
        <div class="section-title">ITENS DA PROPOSTA</div>
        <div class="section-content">
            <table>
                <thead>
                    <tr>
                        <th>Descrição</th>
                        <th style="text-align: right;">Quantidade</th>
                        <th style="text-align: right;">Preço Unitário</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($proposal->items as $item)
                    <tr>
                        <td>{{ $item->description }}</td>
                        <td style="text-align: right;">{{ $item->quantity }}</td>
                        <td style="text-align: right;">{{ number_format($item->unit_price, 2, ',', '.') }} €</td>
                        <td style="text-align: right;">{{ number_format($item->quantity * $item->unit_price, 2, ',', '.') }} €</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="section">
        <div class="section-title">VALIDADE</div>
        <div class="section-content">
            <p>Esta proposta é válida por {{ $proposal->validity_days ?? '30' }} dias a contar da data de emissão.</p>
        </div>
    </div>

    <div class="footer">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Arquivo Digital - Sistema de Gestão</p>
    </div>
</div>
</body>
</html>
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
