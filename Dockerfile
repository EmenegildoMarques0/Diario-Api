# ---------------------------------------------------------
# Etapa 1: Build das dependências PHP
# ---------------------------------------------------------
FROM composer:2 AS build
WORKDIR /var/www

# Copia composer.json e composer.lock
COPY composer.json composer.lock ./

# Instala dependências sem dev e otimiza autoloader
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts || \
    { echo "Erro: Falha ao instalar dependências do Composer"; exit 1; }

# Copia o restante do código da aplicação
COPY . .

# Cria pastas necessárias (logs, cache, views, sessions, api-docs, bootstrap/cache)
RUN mkdir -p /var/www/storage/logs \
    /var/www/storage/api-docs \
    /var/www/storage/framework/cache \
    /var/www/storage/framework/views \
    /var/www/storage/framework/sessions \
    /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    touch /var/www/storage/logs/laravel.log && chmod 664 /var/www/storage/logs/laravel.log && \
    touch /var/www/storage/database.sqlite && chmod 664 /var/www/storage/database.sqlite

# Gera APP_KEY caso não exista
RUN if [ -z "$APP_KEY" ]; then \
    echo "APP_KEY não fornecida, gerando uma nova..." && \
    cp .env.example .env && \
    php artisan key:generate || { echo "Erro: Falha ao gerar APP_KEY"; exit 1; }; \
    else \
    echo "APP_KEY fornecida pelo ambiente, usando valor existente."; \
    fi

# Gera caches do Laravel e Swagger
RUN php artisan config:cache 2> /var/www/storage/logs/config_cache.log || \
    { echo "Erro: Falha ao gerar cache de configuração"; cat /var/www/storage/logs/config_cache.log; exit 1; } && \
    php artisan route:cache 2> /var/www/storage/logs/route_cache.log || \
    { echo "Erro: Falha ao gerar cache de rotas"; cat /var/www/storage/logs/route_cache.log; exit 1; } && \
    php artisan view:cache 2> /var/www/storage/logs/view_cache.log || \
    { echo "Erro: Falha ao gerar cache de views"; cat /var/www/storage/logs/view_cache.log; exit 1; } && \
    php artisan l5-swagger:generate 2> /var/www/storage/logs/swagger_generate.log || \
    { echo "Erro: Falha ao gerar documentação do Swagger"; cat /var/www/storage/logs/swagger_generate.log; exit 1; }

# ---------------------------------------------------------
# Etapa 2: Imagem final com PHP-FPM + Nginx (Alpine)
# ---------------------------------------------------------
FROM php:8.4-fpm-alpine
WORKDIR /var/www

# Instala pacotes e extensões PHP necessárias
RUN apk update && apk add --no-cache \
    nginx supervisor git unzip libzip-dev libpng-dev oniguruma-dev curl postgresql-dev sqlite sqlite-dev \
    py3-setuptools=80.0.0-r0 \
    || { echo "Erro: Falha ao instalar pacotes via apk"; exit 1; } && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql pdo_sqlite mbstring zip bcmath || \
    { echo "Erro: Falha ao instalar extensões do PHP"; exit 1; } && \
    # Verifica se php-fpm e nginx estão disponíveis
    which /usr/local/sbin/php-fpm || { echo "Erro: php-fpm não encontrado"; exit 1; } && \
    which nginx || { echo "Erro: nginx não encontrado"; exit 1; }

# Copia tudo da etapa de build (código + vendor)
COPY --from=build /var/www /var/www

# Cria e garante existência de todos os arquivos de log
RUN mkdir -p /var/www/storage/logs && \
    touch /var/www/storage/logs/nginx-access.log \
    /var/www/storage/logs/nginx-error.log \
    /var/www/storage/logs/php_errors.log \
    /var/www/storage/logs/php-fpm-out.log \
    /var/www/storage/logs/php-fpm-error.log \
    /var/www/storage/logs/laravel.log \
    /var/www/storage/logs/supervisord.log \
    /var/www/storage/logs/nightwatch_agent.log && \
    chown -R www-data:www-data /var/www/storage && \
    chmod -R 775 /var/www/storage && \
    chmod 664 /var/www/storage/logs/*

# Configura permissões para storage e bootstrap/cache
RUN mkdir -p /var/www/storage/framework/{sessions,views,cache} \
    /var/www/storage/api-docs \
    /var/www/storage/app/public \
    /var/www/bootstrap/cache && \
    chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    touch /var/www/storage/database.sqlite && chown www-data:www-data /var/www/storage/database.sqlite && chmod 664 /var/www/storage/database.sqlite

# Configura limites de upload do PHP
RUN echo "upload_max_filesize = 10M" > /usr/local/etc/php/conf.d/uploads.ini && \
    echo "post_max_size = 12M" >> /usr/local/etc/php/conf.d/uploads.ini

# Habilita log de erros do PHP
RUN echo "log_errors = On" > /usr/local/etc/php/conf.d/logging.ini && \
    echo "error_log = /var/www/storage/logs/php_errors.log" >> /usr/local/etc/php/conf.d/logging.ini

# Cria link simbólico do storage (artifacts Laravel)
RUN php artisan storage:link 2> /var/www/storage/logs/storage_link.log || \
    { echo "Erro: Falha ao criar link simbólico"; cat /var/www/storage/logs/storage_link.log; exit 1; }

# Copia arquivos de configuração
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/supervisord.conf /etc/supervisord.conf

# Valida configurações do Nginx e Supervisor
RUN nginx -t || { echo "Erro na configuração do Nginx"; exit 1; } && \
    supervisord -c /etc/supervisord.conf --check || { echo "Erro na configuração do Supervisor"; exit 1; }

# Expõe porta
EXPOSE 8080

# CMD final: roda migrações e inicia supervisord
CMD ["/bin/sh", "-c", "\
    php artisan migrate --force 2> /var/www/storage/logs/migrate.log || { echo 'Erro: Falha ao executar migrações'; cat /var/www/storage/logs/migrate.log; exit 1; } && \
    exec /usr/bin/supervisord -n -c /etc/supervisord.conf \
    "]
