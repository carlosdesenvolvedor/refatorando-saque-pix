# SaquePix - API de Saque via PIX

Uma API para simular operações de uma conta digital, incluindo criação de conta, depósito, cadastro de chaves PIX e solicitação de saques, construída com Hyperf 3.1. O sistema utiliza uma fila (Queue) para processar o envio de e-mails de notificação de forma assíncrona.

## Pré-requisitos

- Docker
- Docker Compose

## Como Executar

1.  Clone este repositório para a sua máquina.
2.  Abra um terminal na raiz do projeto.
3.  Execute o comando para construir e iniciar os contêineres:
    ```bash
    # 1. Instala as dependências localmente (necessário apenas na primeira vez ou após mudar o composer.json)
    docker-compose run --rm --no-deps --entrypoint="" saque-pix-app composer install
    
    # 2. Constrói a imagem e sobe os serviços
    docker-compose up -d --build
    ```
4.  A aplicação estará disponível na URL `http://localhost:9501`.

## Verificando E-mails (MailHog)

Todos os e-mails de notificação de saque são capturados pelo MailHog. Para visualizá-los, acesse a interface web no seu navegador:

- **URL:** `http://localhost:8025`

## Comandos Docker Úteis

- **Ver logs da aplicação:** `docker logs -f saque-pix-app`
- **Parar e remover contêineres e volumes:** `docker-compose down -v`
- **Executar um comando dentro do contêiner da aplicação (ex: abrir um shell):** `docker-compose exec saque-pix-app sh`

## Guia de Uso da API (Fluxo de Teste Completo)

Para testar a funcionalidade principal, siga os passos abaixo utilizando uma ferramenta como **Postman**, **Insomnia** ou `curl`.

---

### Passo 1: Criar uma Conta com E-mail

Primeiro, crie uma conta para o usuário. O `name`, `cpf` e `email` são obrigatórios.

- **Método:** `POST`
- **URL:** `http://localhost:9501/accounts`
- **Body (JSON):**
  ```json
  {
      "name": "Cliente Exemplo",
      "cpf": "12345678900",
      "email": "cliente.exemplo@mailhog.local"
  }
  ```

**Resposta de Sucesso (201 Created):**

> **Importante:** Anote o `id` retornado. Você precisará dele nos próximos passos.

```json
{
    "name": "Cliente Exemplo",
    "updated_at": "2024-05-23T10:00:00.000000Z",
    "created_at": "2024-05-23T10:00:00.000000Z",
    "id": 1
}
```

---

### Passo 2: Cadastrar uma Chave PIX para a Conta

Com o `id` da conta, cadastre uma chave PIX para ela.

- **Método:** `POST`
- **URL:** `http://localhost:9501/pix-keys`
- **Body (JSON):**
    > **Tipos de chave (`kind`):** `cpf`, `email`, `phone`, `random`

    ```json
    {
        "account_id": 1,
        "kind": "email",
        "key": "cliente.exemplo@email.com"
    }
    ```

    **Resposta de Sucesso (201 Created):**

    ```json
    {
        "account_id": "1",
        "kind": "email",
        "key": "cliente.exemplo@email.com",
        "updated_at": "2024-05-23T10:05:00.000000Z",
        "created_at": "2024-05-23T10:05:00.000000Z",
        "id": 1
    }
    ```
    ---

    ### Passo 3: Depositar Saldo na Conta

    Com o `id` da conta em mãos, adicione um saldo a ela.

    - **Método:** `POST`
    - **URL:** `http://localhost:9501/accounts/1/deposit` (substitua `1` pelo `id` da sua conta)
    - **Body (JSON):**
    ```json
    {
        "amount": 500.00
    }
    ```

    **Resposta de Sucesso (200 OK):**

    ```json
    {
        "id": 1,
        "name": "Cliente Exemplo",
        "balance": "500.00",
        "created_at": "...",
        "updated_at": "..."
    }
    ```

    ---

    ### Passo 4: Solicitar um Saque Imediato

    Agora, simule um saque da conta para uma chave PIX de destino.

    - **Método:** `POST`
    - **URL:** `http://localhost:9501/withdrawals`
    - **Body (JSON):**
    ```json
    {
        "account_id": 1,
        "amount": 120.50,
        "pix_key_id": 1
    }
    ```

    **Resposta de Sucesso (201 Created):**

    ```json
    {    <?php
    ```php
    // filepath: [processes.php](http://_vscodecontentref_/0)
    <?php
    
    declare(strict_types=1);
    
    return [
        // Ensure the async queue consumer process is registered so queued jobs are processed
        Hyperf\AsyncQueue\Process\ConsumerProcess::class,
    ];
        "account_id": "1",
        "amount": "120.50",
        "pix_key_id": "1",
        "status": "completed",
        "updated_at": "...",
        "created_at": "...",
        "id": 1
    }
    ```

    ---

    ### Passo 5: Testar um Saque Agendado (Crontab)

    Para verificar se o Crontab está funcionando, você pode agendar um saque para o futuro. O sistema deve processá-lo automaticamente na data e hora especificadas.

    **1. Solicite um Saque Agendado**

    - **Método:** `POST`
    - **URL:** `http://localhost:9501/withdrawals`
    - **Body (JSON):**
    > **Importante:** Use `scheduled_for` e defina o valor para alguns minutos no futuro (ex: `2025-12-25T10:15:00`).

    ```json
    {
        "account_id": 1,
        "amount": 50.00,
        "pix_key_id": 1,
        "scheduled_for": "2025-12-25T10:15:00"
    }
    ```

    **Resposta Esperada (201 Created):**
    O status inicial do saque será `scheduled`. Anote o `id` do saque retornado.

    ```json
    {
        "account_id": "1",
        "amount": "50.00",
        "pix_key_kind": "email",
        "pix_key_key": "cliente.exemplo@email.com",
        "scheduled_for": "2025-12-25T10:15:00.000000Z",
        "status": "scheduled",
        "updated_at": "...",
        "created_at": "...",
        "id": 2
    }
    ```

    **2. Verifique o Status Após o Agendamento**

    Aguarde o horário agendado passar. O Crontab, que roda a cada minuto, deve processar o saque. Você pode verificar os logs para ver a execução da tarefa com `docker logs -f saque-pix-app`.

    Após o processamento, consulte a lista de saques da conta.

    - **Método:** `GET`
    - **URL:** `http://localhost:9501/withdrawals?account_id=1` (substitua `1` pelo `id` da sua conta)

    **Resposta Esperada:**
    O saque que antes estava com status `scheduled` agora deve aparecer como `completed`.

    ---

    ### Outros Endpoints

    - **Consultar Saldo:** `GET /accounts/{accountId}/balance`
    - **Criar Chave PIX:** `POST /pix-keys`
    - **Listar Chaves PIX:** `GET /pix-keys`
