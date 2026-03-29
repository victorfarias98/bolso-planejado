#!/bin/sh
set -e

cd /var/www/html

# Garante diretórios de escrita do Laravel mesmo em runtime read-only/parcial
mkdir -p storage/logs bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache || true
chmod -R ug+rwx storage bootstrap/cache || true

# Garante APP_KEY para artisan (Laravel)
if [ -z "${APP_KEY:-}" ]; then
    export APP_KEY="base64:$(php -r "echo base64_encode(random_bytes(32));")"
    echo "APP_KEY gerada automaticamente para o container (defina APP_KEY no compose em produção)."
fi

# Se usar SQLite, aponte para caminho gravável no contêiner (ephemeral)
if [ "${DB_CONNECTION:-}" = "sqlite" ]; then
    # Caminho default do Laravel ficaria em /var/www/html/database/database.sqlite (camada somente-leitura)
    # Preferimos /tmp/database.sqlite no PaaS.
    if [ -z "${DB_DATABASE:-}" ] || [ "${DB_DATABASE:-}" = "/var/www/html/database/database.sqlite" ] || [ "${DB_DATABASE:-}" = "database/database.sqlite" ]; then
        export DB_DATABASE="/tmp/database.sqlite"
    fi
    if [ ! -f "$DB_DATABASE" ]; then
        echo "Criando SQLite em $DB_DATABASE"
        mkdir -p "$(dirname "$DB_DATABASE")"
        touch "$DB_DATABASE"
    fi
    chmod 666 "$DB_DATABASE" || true
fi

# Limpa caches para evitar rotas/config antigas em deploys de PaaS
if [ -f artisan ]; then
    php artisan optimize:clear >/dev/null 2>&1 || true
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
