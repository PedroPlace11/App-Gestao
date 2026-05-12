<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Comprovativo de Pagamento</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #2563eb; color: #fff; padding: 24px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 28px 32px; }
        .body p { line-height: 1.7; font-size: 14px; margin: 0 0 14px; }
        .invoice-box { background: #f0f4ff; border-left: 4px solid #2563eb; padding: 14px 18px; border-radius: 4px; margin: 20px 0; }
        .invoice-box table { width: 100%; border-collapse: collapse; }
        .invoice-box td { font-size: 13px; padding: 4px 0; color: #444; }
        .invoice-box td:first-child { font-weight: bold; color: #333; width: 160px; }
        .badge { display: inline-block; background: #dcfce7; color: #166534; font-size: 12px; font-weight: bold; padding: 3px 10px; border-radius: 12px; }
        .footer { background: #f8fafc; padding: 18px 32px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #888; }
        .footer a { color: #2563eb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Comprovativo de Pagamento</h1>
            <p>{{ $company_name ?? 'A sua empresa' }}</p>
        </div>
        <div class="body">
            <p>Exmo(a) Sr(a),</p>
            <p>
                Informamos que o pagamento referente à fatura <strong>{{ $invoice->number }}</strong>
                foi registado com sucesso no nosso sistema. Segue em anexo o comprovativo de pagamento em formato PDF.
            </p>

            <div class="invoice-box">
                <table>
                    <tr>
                        <td>Fatura Nº:</td>
                        <td>{{ $invoice->number }}</td>
                    </tr>
                    <tr>
                        <td>Fornecedor:</td>
                        <td>{{ $invoice->supplier->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <td>Data de Emissão:</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->issue_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Data de Vencimento:</td>
                        <td>{{ \Carbon\Carbon::parse($invoice->due_date)->format('d/m/Y') }}</td>
                    </tr>
                    <tr>
                        <td>Valor Total:</td>
                        <td><strong>{{ number_format($invoice->total_value, 2, ',', '.') }} €</strong></td>
                    </tr>
                    <tr>
                        <td>Estado:</td>
                        <td><span class="badge">PAGO</span></td>
                    </tr>
                    @if($paid_at ?? false)
                    <tr>
                        <td>Data de Pagamento:</td>
                        <td>{{ \Carbon\Carbon::parse($paid_at)->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
            </div>

            <p>
                O comprovativo de pagamento encontra-se anexo a este email em formato PDF.
                Por favor guarde este documento para os seus registos.
            </p>

            <p>
                Se tiver alguma questão relativamente a este pagamento, não hesite em contactar-nos.
            </p>

            <p>Com os melhores cumprimentos,<br>
            <strong>{{ $company_name ?? 'A Equipa' }}</strong></p>
        </div>
        <div class="footer">
            <p>Este é um email automático gerado pelo sistema de gestão. Por favor não responda diretamente a este email.</p>
            @if($company_email ?? false)
            <p>Para contacto: <a href="mailto:{{ $company_email }}">{{ $company_email }}</a></p>
            @endif
        </div>
    </div>
</body>
</html>
