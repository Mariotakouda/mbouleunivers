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
    && docker-php-ext-install pdo_pgsql pgsql gd

COPY . .

RUN composer install --no-dev --no-interaction --optimize-autoloader \
    && npm ci \
    && npm run build \
    && npm prune --omit=dev \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache \
    && php artisan storage:link \
    && chmod +x docker/start-web.sh \
    && chown -R application:application storage bootstrap/cache

# Render exécute les migrations à chaque déploiement via la "Pre-Deploy Command" (voir
# render.yaml). Ce CMD ne fait que démarrer le serveur web une fois le déploiement prêt ;
# docker/start-web.sh corrige d'abord les droits sur le disque persistant.
CMD ["docker/start-web.sh"]
