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

# Se a variável PORT existir (ex.: Render), inicia Nginx + PHP-FPM no mesmo container
if [ -n "${PORT:-}" ] && [ -f /etc/nginx/conf.d/default.conf.tpl ]; then
    echo "Detectado ambiente com PORT=${PORT}; iniciando Nginx + PHP-FPM no mesmo container."
    # Renderiza o template do Nginx com a porta dinâmica
    envsubst '${PORT}' < /etc/nginx/conf.d/default.conf.tpl > /etc/nginx/conf.d/default.conf
    # Inicia PHP-FPM em background
    docker-php-entrypoint php-fpm -D
    # Inicia Nginx em foreground
    exec nginx -g 'daemon off;'
fi

# Fallback padrão: apenas PHP-FPM (para uso com Nginx externo no docker-compose)
exec docker-php-entrypoint "$@"
