<!DOCTYPE html>
<html>
<head><title>Comprovante</title></head>
<body>
    <h1>Olá, {{ $withdrawal->account->name ?? 'Cliente' }}</h1>
    <p>Seu saque de <strong>R$ {{ number_format($withdrawal->amount, 2, ',', '.') }}</strong> foi realizado com sucesso.</p>
    <p>Status: {{ $withdrawal->status }}</p>
</body>
</html>