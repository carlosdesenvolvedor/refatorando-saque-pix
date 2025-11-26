# Configurações Iniciais
$BaseUrl = "http://localhost:9501"
$Headers = @{ "Content-Type" = "application/json" }

Clear-Host
Write-Host "`n>>> INICIANDO BATERIA DE TESTES FINAL - SAQUE PIX 2 (PADRAO SENIOR)`n" -ForegroundColor Cyan

# ==============================================================================================
# 1. CRIAR CONTA (UUID)
# ==============================================================================================
Write-Host "[1] Tentando Criar Conta..." -NoNewline
$BodyAccount = @{
    name = "Script Tester Senior"
} | ConvertTo-Json

try {
    $Account = Invoke-RestMethod -Uri "$BaseUrl/accounts" -Method Post -Headers $Headers -Body $BodyAccount
    $UUID = $Account.id
    Write-Host " [SUCESSO] " -ForegroundColor Green
    Write-Host "    -> UUID Gerado: $UUID" -ForegroundColor Gray
} catch {
    Write-Host " [ERRO CRITICO] Nao foi possivel criar a conta." -ForegroundColor Red
    Write-Host $_.Exception.Message
    exit
}

# ==============================================================================================
# 2. DEPOSITAR DINHEIRO
# ==============================================================================================
Write-Host "`n[2] Depositando R$ 1.000,00..." -NoNewline
$BodyDeposit = @{
    amount = 1000.00
} | ConvertTo-Json

try {
    Invoke-RestMethod -Uri "$BaseUrl/account/$UUID/deposit" -Method Post -Headers $Headers -Body $BodyDeposit | Out-Null
    Write-Host " [SUCESSO]" -ForegroundColor Green
} catch {
    Write-Host " [ERRO]" -ForegroundColor Red
    Write-Host $_.Exception.Message
    exit
}

# ==============================================================================================
# 3. SAQUE IMEDIATO (Sucesso)
# ==============================================================================================
Write-Host "`n[3] Testando Saque Imediato (R$ 50,00)..." -NoNewline
$BodyWithdrawNow = @{
    method = "PIX"
    amount = 50.00
    pix = @{
        type = "email"
        key = "imediato@mailhog.local"
    }
    schedule = $null
} | ConvertTo-Json -Depth 5

try {
    $ResNow = Invoke-RestMethod -Uri "$BaseUrl/account/$UUID/balance/withdraw" -Method Post -Headers $Headers -Body $BodyWithdrawNow
    if ($ResNow.done -eq $true -or $ResNow.status -eq 'completed') {
        Write-Host " [SUCESSO]" -ForegroundColor Green
        Write-Host "    -> E-mail deve chegar para: imediato@mailhog.local" -ForegroundColor Gray
    } else {
        Write-Host " [FALHA LOGICA] Status inesperado: $($ResNow.status)" -ForegroundColor Yellow
    }
} catch {
    Write-Host " [ERRO API]" -ForegroundColor Red
    Write-Host $_
}

# ==============================================================================================
# 4. SAQUE AGENDADO (Sucesso - Daqui a 2 min)
# ==============================================================================================
$FutureDate = (Get-Date).AddMinutes(2).ToString("yyyy-MM-dd HH:mm:ss")
Write-Host "`n[4] Agendando Saque (R$ 100,00) para $FutureDate..." -NoNewline

$BodyWithdrawFuture = @{
    method = "PIX"
    amount = 100.00
    pix = @{
        type = "cpf"
        key = "12345678900"
    }
    schedule = $FutureDate
} | ConvertTo-Json -Depth 5

try {
    $ResFuture = Invoke-RestMethod -Uri "$BaseUrl/account/$UUID/balance/withdraw" -Method Post -Headers $Headers -Body $BodyWithdrawFuture
    
    if ($ResFuture.scheduled -eq $true -or $ResFuture.status -eq 'scheduled') {
        Write-Host " [SUCESSO]" -ForegroundColor Green
        Write-Host "    -> Agendado! Verifique logs/MailHog em 2 minutos." -ForegroundColor Gray
    } else {
        Write-Host " [FALHA] O saque nao ficou como agendado!" -ForegroundColor Red
    }
} catch {
    Write-Host " [ERRO]" -ForegroundColor Red
    Write-Host $_
}

# ==============================================================================================
# 5. TESTE DE VALIDACAO (Regra dos 7 dias)
# ==============================================================================================
$FarFutureDate = (Get-Date).AddDays(8).ToString("yyyy-MM-dd HH:mm:ss")
Write-Host "`n[5] Testando Bloqueio de Data Longa (+8 dias)..." -NoNewline

$BodyFailDate = @{
    method = "PIX"
    amount = 10.00
    pix = @{ type = "email"; key = "teste@fail.com" }
    schedule = $FarFutureDate
} | ConvertTo-Json -Depth 5

try {
    Invoke-RestMethod -Uri "$BaseUrl/account/$UUID/balance/withdraw" -Method Post -Headers $Headers -Body $BodyFailDate
    Write-Host " [FALHA]" -ForegroundColor Red
    Write-Host "    -> A API aceitou data > 7 dias!" -ForegroundColor Yellow
} catch {
    if ($_.Exception.Response.StatusCode.value__ -eq 422) {
        Write-Host " [SUCESSO] (Bloqueado corretamente - 422)" -ForegroundColor Green
    } else {
        Write-Host " [ERRO ESTRANHO] Code: $($_.Exception.Response.StatusCode.value__)" -ForegroundColor Yellow
    }
}

# ==============================================================================================
# 6. TESTE DE SALDO INSUFICIENTE (A Prova Final)
# ==============================================================================================
Write-Host "`n[6] Testando Saldo Insuficiente (Tentando sacar R$ 1.000.000)..." -NoNewline
$BodyNoMoney = @{
    method = "PIX"
    amount = 1000000.00
    pix = @{ type = "email"; key = "rico@fail.com" }
    schedule = $null
} | ConvertTo-Json -Depth 5

try {
    Invoke-RestMethod -Uri "$BaseUrl/account/$UUID/balance/withdraw" -Method Post -Headers $Headers -Body $BodyNoMoney
    Write-Host " [FALHA]" -ForegroundColor Red
    Write-Host "    -> A API permitiu sacar sem saldo ou deu erro 500!" -ForegroundColor Yellow
} catch {
    $Code = $_.Exception.Response.StatusCode.value__
    if ($Code -eq 422 -or $Code -eq 400) {
        Write-Host " [SUCESSO] (Bloqueado corretamente - Code $Code)" -ForegroundColor Green
    } elseif ($Code -eq 500) {
        Write-Host " [FALHA] Deu Erro 500 (Internal Server Error) - Faltou o Exception Handler!" -ForegroundColor Red
    } else {
        Write-Host " [ERRO] Code inesperado: $Code" -ForegroundColor Red
    }
}

Write-Host "`n>>> TESTES FINALIZADOS! SE TUDO ESTIVER VERDE, VOCE PASSOU! !!!" -ForegroundColor Cyan