<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Contrato - {{ $document['name'] ?? '' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1f2937; line-height: 1.6; }
        .page { padding: 30px; }
        .header { border-bottom: 2px solid #0b72b9; padding-bottom: 15px; margin-bottom: 20px; }
        .header h1 { color: #0b72b9; font-size: 24px; font-weight: 700; }
        .section { margin-bottom: 20px; }
        .section-title { background: #0b72b9; color: #fff; padding: 6px 8px; font-size: 10px; font-weight: 700; margin-bottom: 8px; }
        .section-content { padding: 10px; border: 1px solid #bfdbfe; }
        .detail-row { display: flex; justify-content: space-between; margin-bottom: 6px; }
        .label { font-weight: 600; }
        .value { color: #4b5563; }
        .footer { margin-top: 30px; border-top: 1px solid #ccc; padding-top: 15px; text-align: center; color: #666; font-size: 9px; }
    </style>
</head>
<body>
<div class="page">
    <div class="header">
        <h1>CONTRATO DE SERVIÇOS</h1>
    </div>

    <div class="section">
        <div class="section-title">INFORMAÇÕES DO CONTRATO</div>
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
                <span class="label">Data:</span>
                <span class="value">{{ $document['date'] ?? '-' }}</span>
            </div>
            <div class="detail-row">
                <span class="label">Status:</span>
                <span class="value">{{ $document['status'] ?? '-' }}</span>
            </div>
        </div>
    </div>

    <div class="section">
        <div class="section-title">TERMOS E CONDIÇÕES</div>
        <div class="section-content">
            <p>Este é um contrato de exemplo gerado a partir do arquivo digital do sistema.</p>
            <p style="margin-top: 10px;">As partes acordam em cumprir com os termos e condições aqui estabelecidos.</p>
            <p style="margin-top: 10px;">Este documento tem caráter ilustrativo e pode ser utilizado como referência para contratos reais.</p>
        </div>
    </div>

    <div class="footer">
        <p>Documento gerado em {{ now()->format('d/m/Y H:i') }}</p>
        <p>Arquivo Digital - Sistema de Gestão</p>
    </div>
</div>
</body>
</html>
