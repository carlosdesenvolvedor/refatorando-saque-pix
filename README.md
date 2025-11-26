# 🏦 SaquePix2 - API de Conta Digital de Alta Performance

## 🎯 Sobre o Projeto

O **SaquePix2** é uma API de Conta Digital robusta e escalável, projetada para processar transações financeiras com **alta performance** e **baixa latência**. Construída sobre o framework **Hyperf** (baseado em Swoole/Corrotinas), a aplicação adota uma arquitetura orientada a microsserviços e eventos.

O sistema gerencia o ciclo de vida completo de uma conta digital, incluindo criação, depósitos e, principalmente, **saques via PIX** (imediatos e agendados). A solução implementa filas assíncronas para notificações e tarefas agendadas (Cron) para processamento de transações futuras, garantindo que a thread principal da API permaneça livre para atender novas requisições.

---

## 🛠 Stack Tecnológica

A stack foi escolhida para maximizar a concorrência e a eficiência de recursos:

- **Linguagem:** PHP 8.2
- **Framework:** Hyperf 3.1 (Swoole/Coroutines)
- **Banco de Dados:** MySQL 8.0
- **Cache & Filas:** Redis (Async Queue)
- **Containerização:** Docker & Docker Compose
- **Testes de E-mail:** MailHog

---

## 🏗 Decisões de Arquitetura

Como Tech Lead, as seguintes decisões foram tomadas para garantir robustez, segurança e manutenibilidade:

### 1. 🆔 UUIDs (Universally Unique Identifiers)
Adotamos UUIDs (v4) como chaves primárias em todas as tabelas (`accounts`, `account_withdraws`, etc.).
- **Porquê:** Garante unicidade global, dificulta a enumeração de registros por atacantes (security through obscurity) e facilita a distribuição de dados (sharding) em cenários futuros de escala horizontal.

### 2. ⚡ Filas Assíncronas (Redis)
O envio de e-mails transacionais é desacoplado da requisição HTTP principal.
- **Porquê:** Enviar e-mail é uma operação lenta e propensa a falhas de rede. Ao mover essa responsabilidade para um *Job* no Redis, a API responde instantaneamente ao usuário (`201 Created`), enquanto o "Worker" processa o envio em background, melhorando drasticamente a experiência do usuário e o throughput da API.

### 3. ⏰ Crontab & Agendamento
Saques agendados não bloqueiam recursos. Eles são persistidos no banco e processados por uma tarefa Cron (`ProcessScheduledWithdrawals`) que roda a cada minuto.
- **Porquê:** Permite o agendamento flexível de transações sem manter conexões abertas. A lógica de negócio no Cron garante atomicidade e consistência, verificando saldo e executando a transação no momento exato.

### 4. 🛡️ Centralized Exception Handling
Implementamos um tratamento global de exceções (`BusinessExceptionHandler`).
- **Porquê:** Diferenciamos claramente erros de negócio (ex: "Saldo Insuficiente") de erros de sistema. Erros de negócio retornam **HTTP 422 Unprocessable Entity** com uma mensagem clara em JSON, enquanto erros inesperados retornam **500**. Isso facilita a integração por parte do front-end e mantém os logs limpos.

---

## 🚀 Guia de Instalação

Siga os passos abaixo para rodar o projeto localmente:

### Pré-requisitos
- Docker e Docker Compose instalados.

### Passo a Passo

1. **Subir os containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Executar as Migrations (Criação das tabelas):**
   ```bash
   docker-compose exec saque-pix-app php bin/hyperf.php migrate
   ```

3. **Acessar a Aplicação:**
   - **API:** `http://localhost:9501`
   - **MailHog (E-mails):** `http://localhost:8025`

---

## 📖 Documentação da API

Abaixo estão os principais endpoints para interagir com o sistema.

### 1. Criar Conta
Cria uma nova conta digital com saldo inicial zero.

- **Endpoint:** `POST /accounts`
- **Body:**
  ```json
  {
    "name": "Carlos Desenvolvedor",
    "document": "12345678900",
    "email": "carlos@example.com"
  }
  ```
- **Resposta (201 Created):**
  ```json
  {
    "id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "name": "Carlos Desenvolvedor",
    "balance": "0.00",
    ...
  }
  ```

### 2. Realizar Depósito
Adiciona saldo a uma conta existente.

- **Endpoint:** `POST /accounts/{uuid}/deposit`
- **Body:**
  ```json
  {
    "amount": 100.50
  }
  ```
- **Resposta (200 OK):**
  ```json
  {
    "account_id": "a1b2c3d4-e5f6-7890-1234-567890abcdef",
    "current_balance": "100.50"
  }
  ```

### 3. Solicitar Saque (PIX)
Realiza um saque imediato ou agendado.

- **Endpoint:** `POST /accounts/{uuid}/withdraw`
- **Body (Saque Imediato):**
  ```json
  {
    "method": "PIX",
    "amount": 50.00,
    "pix": {
      "type": "email",
      "key": "chave@pix.com"
    },
    "schedule": null
  }
  ```

- **Body (Saque Agendado):**
  *A data deve ser futura e no máximo até 7 dias.*
  ```json
  {
    "method": "PIX",
    "amount": 50.00,
    "pix": {
      "type": "cpf",
      "key": "12345678900"
    },
    "schedule": "2025-12-01 10:00:00"
  }
  ```

- **Resposta de Erro (Ex: Saldo Insuficiente - 422):**
  ```json
  {
    "message": "Saldo insuficiente",
    "code": 422
  }
  ```
  ## ✅ Qualidade Assegurada (Testes E2E)

O projeto inclui uma suíte de testes automatizados (`tests/e2e_test.ps1`) que valida todos os cenários críticos:
1. Criação de Conta e Validação de UUID.
2. Depósito e Atualização de Saldo.
3. Saque Imediato (Integração com MailHog).
4. Saque Agendado (Validação de Cron).
5. Regras de Negócio (Bloqueio de data > 7 dias e Saldo Insuficiente).

### Evidência de Execução:
![Testes Automatizados](.github/images/evidence.png)