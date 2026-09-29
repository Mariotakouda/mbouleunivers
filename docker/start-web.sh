#!/bin/sh
# Exécuté à CHAQUE démarrage du service web sur Render.
set -e

mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/logs storage/app/public bootstrap/cache

# Plan gratuit : Render n'offre pas de « Pre-Deploy Command », donc les migrations sont lancées ici.
# Elles sont idempotentes (rien ne se passe si la base est déjà à jour).
php artisan migrate --force

# Crée le premier administrateur (ADMIN_EMAIL / ADMIN_PASSWORD) s'il n'en existe aucun.
php artisan admin:create

# Les commandes ci-dessus tournent en root : on rend les fichiers créés (logs, cache) à l'utilisateur
# « application » qui fait tourner PHP-FPM, sinon le site ne pourrait plus écrire dans storage/.
chown -R application:application storage bootstrap/cache

exec supervisord
