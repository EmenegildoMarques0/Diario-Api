# Etapa 1: Build das dependências PHP
FROM composer:2.8 AS build
WORKDIR /var/www
# Copia os arquivos de dependências
COPY composer.json composer.lock ./
# Instala dependências, sem dependências de desenvolvimento
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts || \
    { echo "Erro: Falha ao instalar dependências do Composer"; exit 1; }
# Copia o restante do código da aplicação
COPY . .
# Configura permissões para pastas de armazenamento e cache
RUN mkdir -p /var/www/storage/logs /var/www/storage/framework/cache /var/www/storage/framework/views /var/www/storage/framework/sessions /var/www/bootstrap/cache && \
    chown -R $(whoami):$(whoami) /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    touch /var/www/storage/logs/laravel.log && \
    chown $(whoami):$(whoami) /var/www/storage/logs/laravel.log && \
    chmod 664 /var/www/storage/logs/laravel.log
# Cria .env e gera APP_KEY
RUN cp .env.example .env && \
    php artisan key:generate || \
    { echo "Erro: Falha ao gerar APP_KEY"; exit 1; }
# Gera caches do Laravel
RUN php artisan config:cache 2> /var/www/storage/logs/config_cache.log || \
    { echo "Erro: Falha ao gerar cache de configuração"; cat /var/www/storage/logs/config_cache.log; exit 1; } && \
    php artisan route:cache 2> /var/www/storage/logs/route_cache.log || \
    { echo "Erro: Falha ao gerar cache de rotas"; cat /var/www/storage/logs/route_cache.log; exit 1; } && \
    php artisan view:cache 2> /var/www/storage/logs/view_cache.log || \
    { echo "Erro: Falha ao gerar cache de views"; cat /var/www/storage/logs/view_cache.log; exit 1; }

# Etapa 2: Imagem final com PHP-FPM
FROM php:8.4-fpm
# Instala dependências do sistema e extensões PHP necessárias
RUN apt-get update && \
    apt-get install -y --no-install-recommends nginx supervisor libzip-dev libpng-dev && \
    docker-php-ext-install pdo pdo_mysql zip bcmath && \
    apt-get clean && rm -rf /var/lib/apt/lists/* || \
    { echo "Erro: Falha ao instalar pacotes"; exit 1; }
WORKDIR /var/www
# Copia os arquivos da etapa de build
COPY --from=build /var/www /var/www
# Configura permissões para o usuário www-data
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    mkdir -p /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/storage/framework/cache && \
    chown -R www-data:www-data /var/www/storage/framework && \
    chmod -R 775 /var/www/storage/framework && \
    touch /var/www/storage/logs/laravel.log && \
    chown www-data:www-data /var/www/storage/logs/laravel.log && \
    chmod 664 /var/www/storage/logs/laravel.log
# Copia arquivo de configuração do Nginx
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/supervisord.conf /etc/supervisord.conf
# Cria diretório temporário para uploads
RUN mkdir -p /tmp/nginx_client_body && \
    chown www-data:www-data /tmp/nginx_client_body && \
    chmod 700 /tmp/nginx_client_body
EXPOSE 8080
# Inicia o Supervisor
CMD ["/bin/sh", "-c", "php artisan migrate --force 2> /var/www/storage/logs/migrate.log || { echo 'Erro: Falha ao executar migrations'; cat /var/www/storage/logs/migrate.log; exit 1; } && /usr/bin/supervisord -c /etc/supervisord.conf"]
