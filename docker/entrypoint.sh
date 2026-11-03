#!/bin/sh
set -e

cd /var/www/html

# Dependências PHP (primeira execução)
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

# Arquivo de ambiente e chave da aplicação
[ -f .env ] || cp .env.example .env
if ! grep -q '^APP_KEY=base64' .env; then
    php artisan key:generate --force
fi

chown -R www-data:www-data storage bootstrap/cache

# Banco de dados: cria as tabelas e, na primeira vez, carrega os dados de exemplo
php artisan migrate --force
if [ ! -f storage/app/.seeded ]; then
    php artisan db:seed --force
    touch storage/app/.seeded
fi

exec "$@"
