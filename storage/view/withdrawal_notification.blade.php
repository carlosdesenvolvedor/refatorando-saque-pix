<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Notificação de Saque</title>
</head>
<body style="font-family: Arial, sans-serif;">
    <h2>Olá, {{ $name }}!</h2>

    <p>Recebemos uma atualização sobre sua solicitação de saque PIX.</p>

    <ul>
        <li><strong>Valor:</strong> R$ {{ number_format($amount, 2, ',', '.') }}</li>
        <li><strong>Data da Solicitação:</strong> {{ $date }}</li>
        <li><strong>Status:</strong> {{ $status }}</li>
    </ul>

    <p>Atenciosamente,<br>Equipe SaquePix2</p>
</body>
</html>