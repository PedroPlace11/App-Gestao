<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Fatura - {{ $document['name'] ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; }

        .page { border: 1px solid #9ca3af; padding: 18px; }
        .headline { color: #3b82c4; font-size: 44px; letter-spacing: 1px; font-weight: 700; margin-bottom: 10px; text-align: center; }

        .top { width: 100%; margin-bottom: 16px; }
        .left, .right { width: 48%; display: inline-block; vertical-align: top; }
        .right { text-align: right; }

        .logo { color: #1d4ed8; font-size: 30px; font-weight: 700; margin-bottom: 8px; }
        .box-title { background: #0b72b9; color: #fff; font-size: 11px; padding: 6px 8px; margin-top: 8px; }
        .box-body { border: 1px solid #bfdbfe; border-top: none; padding: 8px; min-height: 72px; }

        .info-row { margin-bottom: 4px; }

        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        thead th { background: #0b72b9; color: #fff; font-size: 11px; padding: 6px; border: 1px solid #93c5fd; }
        tbody td { border: 1px solid #bfdbfe; padding: 6px; }
        .right-text { text-align: right; }

        .totals { width: 42%; margin-left: auto; margin-top: 16px; }
        .totals td { border: 1px solid #bfdbfe; padding: 7px; }
        .totals .label { background: #dbeafe; font-weight: 600; }
        .totals .grand { background: #bfdbfe; font-size: 13px; font-weight: 700; }

        .thanks { margin-top: 24px; text-align: center; color: #0b72b9; font-size: 30px; font-weight: 300; }
        .footer { margin-top: 12px; text-align: center; color: #2563eb; font-size: 10px; line-height: 1.5; }
    </style>
</head>
<body>
<div class="page">
    <div class="top">
        <div class="left">
            <div class="logo">Inovcorp</div>
            <div class="box-title">EMPRESA</div>
            <div class="box-body">
                <div class="info-row"><strong>Inovcorp, Lda.</strong></div>
                <div class="info-row">NIF: 123456789</div>
                <div class="info-row">Morada: Rua da Inovação, 100</div>
                <div class="info-row">Porto, Portugal</div>
            </div>
        </div>
        <div class="right">
            <div class="headline">FATURA</div>
            <div class="box-title">INFORMAÇÃO DO DOCUMENTO</div>
            <div class="box-body">
                <div class="info-row"><strong>{{ $document['name'] ?? '-' }}</strong></div>
                <div class="info-row">Data: {{ $document['date'] ?? '-' }}</div>
                <div class="info-row">Status: {{ $document['status'] ?? '-' }}</div>
            </div>
        </div>
    </div>

    <div class="top">
        <div class="left">
            <div class="box-title">CLIENTE/ENTIDADE</div>
            <div class="box-body">
                <div class="info-row"><strong>{{ $document['entity'] ?? '-' }}</strong></div>
            </div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>Descrição</th>
                <th class="right-text">Qtd</th>
                <th class="right-text">Preço Unit.</th>
                <th class="right-text">Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>Serviço de Exemplo</td>
                <td class="right-text">1</td>
                <td class="right-text">1.000,00 €</td>
                <td class="right-text">1.000,00 €</td>
            </tr>
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td class="label">Subtotal:</td>
            <td class="right-text">1.000,00 €</td>
        </tr>
        <tr>
            <td class="label">IVA (23%):</td>
            <td class="right-text">230,00 €</td>
        </tr>
        <tr>
            <td class="label grand">TOTAL:</td>
            <td class="right-text grand">1.230,00 €</td>
        </tr>
    </table>

    <div class="thanks">Obrigado!</div>
    <div class="footer">
        <p>Documento de arquivo digital gerado em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Sistema de Gestão - Inovcorp</p>
    </div>
</div>
</body>
</html>
