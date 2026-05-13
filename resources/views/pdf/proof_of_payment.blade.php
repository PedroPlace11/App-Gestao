<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Comprovativo de Pagamento - {{ $document['name'] ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
        .page { padding: 30px; }
        .header { border-bottom: 2px solid #059669; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #059669; font-size: 24px; font-weight: 700; }
        .section { margin-bottom: 20px; }
        .section-title { background: #059669; color: #fff; padding: 6px 8px; font-size: 10px; font-weight: 700; margin-bottom: 8px; }
        .section-content { padding: 10px; border: 1px solid #dcfce7; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .label { font-weight: 600; }
        .value { color: #4b5563; }
        .stamp { background: #f0fdf4; border: 2px solid #059669; padding: 15px; text-align: center; margin: 15px 0; }
        .stamp-text { color: #059669; font-weight: 700; font-size: 14px; }
        .footer { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 15px; text-align: center; color: #666; font-size: 9px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>COMPROVATIVO DE PAGAMENTO</h1>
    </div>

    <div class="section">
        <div class="section-title">DADOS DO COMPROVATIVO</div>
        <div class="section-content">
            <div class="detail-row">
                <span class="label">Documento:</span>
                <span class="value">{{ $document['name'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Entidade:</span>
                <span class="value">{{ $document['entity'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Data do Pagamento:</span>
                <span class="value">{{ $document['date'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value">{{ $document['status'] ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="stamp">
        <div class="stamp-text">✓ PAGAMENTO CONFIRMADO</div>
    </div>

    <div class="section">
        <div class="section-title">NOTAS</div>
        <div class="section-content">
            <p>Este comprovativo atesta o recebimento e processamento do pagamento relativo à fatura ou serviço especificado.</p>
            <p style="margin-top: 10px;">Guarde este documento para fins de auditoria e conformidade fiscal.</p>
        </div>
    </div>

    <div class="footer">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Arquivo Digital - Sistema de Gestão</p>
    </div>
</div>
</body>
</html>
