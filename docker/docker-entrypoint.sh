#!/bin/sh
set -e

cd /var/www/html

# Garante APP_KEY para artisan (Laravel)
if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="base64:$(php -r "echo base64_encode(random_bytes(32));")"
    echo "APP_KEY gerada automaticamente para o container (defina APP_KEY no compose em produção)."
fi

# Migrações com retry (MySQL pode ainda não aceitar conexões no primeiro segundo)
if [ -f artisan ]; then
    i=0
    while [ "$i" -lt 30 ]; do
        if php artisan migrate --force 2>/dev/null; then
            break
        fi
        i=$((i + 1))
        sleep 2
    done
fi

exec docker-php-entrypoint "$@"
