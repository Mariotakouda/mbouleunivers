# Image "tout-en-un" (Nginx + PHP-FPM + Supervisor) qui écoute automatiquement sur la
# variable $PORT fournie par Render — c'est ce qui évite d'écrire sa propre config Nginx.
FROM webdevops/php-nginx:8.3-alpine

ENV WEB_DOCUMENT_ROOT=/app/public \
    APP_ENV=production \
    APP_DEBUG=false

WORKDIR /app

# Outils manquants dans l'image de base : Node (pour compiler les assets) et les
# extensions PHP nécessaires à PostgreSQL et aux QR codes/PDF des billets (gd).
RUN apk add --no-cache nodejs npm \
    && apk add --no-cache postgresql-dev libpng-dev libjpeg-turbo-dev freetype-dev \
    && ( php -m | grep -qi '^pdo_pgsql$' || docker-php-ext-install pdo_pgsql pgsql ) \
    && ( php -m | grep -qi '^gd$' || ( docker-php-ext-configure gd --with-freetype --with-jpeg && docker-php-ext-install gd ) ) \
    && php -m | grep -i pdo_pgsql \
    && php -m | grep -i '^gd$'

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader \
    && npm ci \
    && npm run build \
    && npm prune --omit=dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache \
    && php artisan storage:link \
    && chmod +x docker/start-web.sh \
    && chown -R application:application storage bootstrap/cache

# Plan gratuit Render : pas de "Pre-Deploy Command", donc docker/start-web.sh lance les
# migrations, crée le premier administrateur puis démarre le serveur web.
CMD ["docker/start-web.sh"]
