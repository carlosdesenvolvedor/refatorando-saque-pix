FROM hyperf/hyperf:8.2-alpine-v3.18-swoole

LABEL maintainer="Hyperf Developers <group@hyperf.io>"

WORKDIR /app

# Apenas dependências úteis
RUN apk update && apk add --no-cache mysql-client

# Copia o composer da imagem oficial
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copia os arquivos do projeto para o diretório de trabalho atual (/app)
COPY . .

# Instala dependências PHP do Composer
RUN composer install -o

EXPOSE 9501

CMD ["php", "/app/bin/hyperf.php", "start"]