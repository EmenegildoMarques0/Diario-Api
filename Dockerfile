# Etapa 1: Build das dependências PHP
FROM composer:2.8 AS build
WORKDIR /var/www
# Copia os arquivos de dependências
COPY composer.json composer.lock ./
# Instala dependências, incluindo l5-swagger, sem dependências de desenvolvimento
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist --no-scripts || \
    { echo "Erro: Falha ao instalar dependências do Composer"; exit 1; }
# Copia o restante do código da aplicação
COPY . .
# Configura permissões e cria pastas necessárias para cache, views, sessões e banco SQLite
RUN mkdir -p /var/www/storage/logs /var/www/storage/api-docs /var/www/storage/framework/cache /var/www/storage/framework/views /var/www/storage/framework/sessions /var/www/bootstrap/cache && \
    chown -R $(whoami):$(whoami) /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    touch /var/www/storage/logs/laravel.log && \
    chown $(whoami):$(whoami) /var/www/storage/logs/laravel.log && \
    chmod 664 /var/www/storage/logs/laravel.log && \
    touch /var/www/storage/database.sqlite && \
    chown $(whoami):$(whoami) /var/www/storage/database.sqlite && \
    chmod 664 /var/www/storage/database.sqlite
# Cria um .env temporário e gera a APP_KEY
RUN cp .env.example .env && \
    php artisan key:generate || \
    { echo "Erro: Falha ao gerar APP_KEY"; exit 1; }
# Gera caches do Laravel e documentação Swagger com logs de erro
RUN php artisan config:cache 2> /var/www/storage/logs/config_cache.log || \
    { echo "Erro: Falha ao gerar cache de configuração. Veja /var/www/storage/logs/config_cache.log"; cat /var/www/storage/logs/config_cache.log; exit 1; } && \
    php artisan route:cache 2> /var/www/storage/logs/route_cache.log || \
    { echo "Erro: Falha ao gerar cache de rotas. Veja /var/www/storage/logs/route_cache.log"; cat /var/www/storage/logs/route_cache.log; exit 1; } && \
    php artisan view:cache 2> /var/www/storage/logs/view_cache.log || \
    { echo "Erro: Falha ao gerar cache de views. Veja /var/www/storage/logs/view_cache.log"; cat /var/www/storage/logs/view_cache.log; exit 1; } && \
    php artisan l5-swagger:generate 2> /var/www/storage/logs/swagger_generate.log || \
    { echo "Erro: Falha ao gerar documentação do Swagger. Veja /var/www/storage/logs/swagger_generate.log"; cat /var/www/storage/logs/swagger_generate.log; exit 1; }

# Etapa 2: Imagem final com PHP-FPM + Nginx
FROM php:8.4-fpm-alpine
# Atualiza repositórios e instala dependências do sistema e extensões PHP para MySQL, PostgreSQL e SQLite
RUN apk update && \
    apk add --no-cache nginx supervisor git unzip libzip-dev libpng-dev oniguruma-dev curl postgresql-dev sqlite sqlite-dev || \
    { echo "Erro: Falha ao instalar pacotes via apk"; exit 1; } && \
    docker-php-ext-install pdo pdo_mysql pdo_pgsql pgsql pdo_sqlite mbstring zip bcmath || \
    { echo "Erro: Falha ao instalar extensões do PHP"; exit 1; }
WORKDIR /var/www
# Copia os arquivos da etapa de build
COPY --from=build /var/www /var/www
# Configura permissões para o usuário www-data, garantindo escrita no SQLite
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R 775 /var/www/storage /var/www/bootstrap/cache && \
    touch /var/www/storage/logs/laravel.log && \
    chown www-data:www-data /var/www/storage/logs/laravel.log && \
    chmod 664 /var/www/storage/logs/laravel.log && \
    mkdir -p /var/www/storage/framework/sessions /var/www/storage/framework/views /var/www/storage/framework/cache /var/www/storage/api-docs && \
    chown -R www-data:www-data /var/www/storage/framework /var/www/storage/api-docs && \
    chmod -R 775 /var/www/storage/framework /var/www/storage/api-docs && \
    touch /var/www/storage/database.sqlite && \
    chown www-data:www-data /var/www/storage/database.sqlite && \
    chmod 664 /var/www/storage/database.sqlite && \
    chgrp -R www-data /var/www/storage /var/www/bootstrap/cache && \
    chmod -R ug+rwx /var/www/storage /var/www/bootstrap/cache
# Copia arquivos de configuração
COPY ./deploy/nginx.conf /etc/nginx/nginx.conf
COPY ./deploy/supervisord.conf /etc/supervisord.conf
EXPOSE 8080
# Executa migrations e inicia o Supervisor
CMD ["/bin/sh", "-c", "php artisan migrate --force 2> /var/www/storage/logs/migrate.log || { echo 'Erro: Falha ao executar migrations. Veja /var/www/storage/logs/migrate.log'; cat /var/www/storage/logs/migrate.log; exit 1; } && /usr/bin/supervisord -c /etc/supervisord.conf || { echo 'Erro: Falha ao iniciar o Supervisor'; exit 1; }"]
