<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>PIX Agendado</title>
</head>
<body>
    <h1>Olá, {{ $name ?? 'Cliente' }}!</h1>
    <p>Seu PIX no valor de <strong>R$ {{ number_format($amount ?? 0, 2, ',', '.') }}</strong> foi agendado com sucesso.</p>
    <p>Ele será processado na data: <strong>{{ $scheduled_to ?? 'N/A' }}</strong>.</p>
    <p>Obrigado por usar nossos serviços!</p>
</body>
</html>
