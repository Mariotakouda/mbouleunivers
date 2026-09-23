#!/bin/sh
# Exécuté à CHAQUE démarrage du service web sur Render.
set -e

# Le disque persistant Render est monté sur storage/app/public : on s'assure qu'il
# appartient à l'utilisateur "application" (celui qui fait tourner PHP-FPM dans cette
# image), sinon les envois d'affiches et la génération des QR codes échoueraient.
mkdir -p storage/app/public/events storage/app/public/qrcodes
chown -R application:application storage bootstrap/cache

# Le lien public/storage -> storage/app/public est déjà créé au moment du build
# (voir Dockerfile), donc rien à refaire ici.

exec supervisord
