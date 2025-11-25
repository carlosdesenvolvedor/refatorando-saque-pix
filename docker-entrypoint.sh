#!/bin/sh

# Exit immediately if a command exits with a non-zero status.
set -e

# Wait for the database to be ready.
echo "Aguardando o banco de dados iniciar..."

# A simple loop to wait for the DB. A more robust solution might use dockerize or wait-for-it.sh
sleep 10

echo "Otimizando o autoloader do Composer..."
composer dump-autoload -o

echo "Executando as migrations do banco de dados..."
php bin/hyperf.php migrate

echo "Iniciando o servidor Hyperf..."
php /app/bin/hyperf.php start