<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Bem-vindo(a) ao Inovcorp</title>
    <style>
        body { font-family: Arial, sans-serif; color: #333; margin: 0; padding: 0; background-color: #f4f4f4; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .header { background-color: #2563eb; color: #fff; padding: 28px 32px; }
        .header h1 { margin: 0; font-size: 22px; }
        .header p { margin: 4px 0 0; font-size: 14px; opacity: 0.85; }
        .body { padding: 28px 32px; }
        .body p { line-height: 1.7; font-size: 14px; margin: 0 0 14px; }
        .credentials-box { background: #f0f4ff; border-left: 4px solid #2563eb; padding: 16px 20px; border-radius: 4px; margin: 20px 0; }
        .credentials-box table { width: 100%; border-collapse: collapse; }
        .credentials-box td { font-size: 13px; padding: 5px 0; color: #444; }
        .credentials-box td:first-child { font-weight: bold; color: #333; width: 120px; }
        .credentials-box .pwd { font-family: monospace; font-size: 14px; background: #e0e7ff; padding: 2px 8px; border-radius: 3px; letter-spacing: 1px; }
        .btn { display: inline-block; background: #2563eb; color: #fff; text-decoration: none; padding: 12px 28px; border-radius: 6px; font-size: 14px; font-weight: bold; margin: 8px 0; }
        .footer { background: #f8fafc; padding: 18px 32px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #888; }
        .warning { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 10px 14px; border-radius: 4px; font-size: 13px; color: #92400e; margin-top: 12px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Bem-vindo(a) ao Inovcorp</h1>
            <p>A sua conta foi criada com sucesso</p>
        </div>
        <div class="body">
            <p>Caro(a) <strong>{{ $user->name }}</strong>,</p>

            <p>
                A sua conta de acesso ao sistema de gestão <strong>Inovcorp</strong> foi criada com sucesso.
                Abaixo encontra as suas credenciais de acesso.
            </p>

            <div class="credentials-box">
                <table>
                    <tr>
                        <td>Email:</td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td>Password:</td>
                        <td><span class="pwd">{{ $plainPassword }}</span></td>
                    </tr>
                </table>
            </div>

            <div class="warning">
                <strong>⚠ Importante:</strong> Por razões de segurança, recomendamos que altere a sua password
                imediatamente após o primeiro acesso.
            </div>

            <p style="margin-top: 20px;">
                <a href="{{ $loginUrl }}" class="btn">Aceder ao Sistema</a>
            </p>

            <p>
                Se tiver alguma dificuldade no acesso, não hesite em contactar o administrador do sistema.
            </p>

            <p>Com os melhores cumprimentos,<br><strong>Equipa Inovcorp</strong></p>
        </div>
        <div class="footer">
            Este email foi gerado automaticamente. Por favor, não responda diretamente a este email.
        </div>
    </div>
</body>
</html>
