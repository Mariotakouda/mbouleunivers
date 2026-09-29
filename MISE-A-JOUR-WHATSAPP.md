# Mise à jour d'Univers 2 M'boulè : commande via WhatsApp + hébergement gratuit Render

## Installer (sur votre projet existant)
1. Copiez le contenu de ce dossier PAR-DESSUS votre projet (mêmes chemins), en gardant votre `.env`, `.git`, `vendor` et `node_modules`.
2. **Supprimez** ces fichiers devenus inutiles :
   - `app/Services/PayGateService.php`
   - `app/Http/Controllers/Public/PaymentController.php`
   - `resources/views/public/payment-show.blade.php`, `payment-success.blade.php`, `payment-failed.blade.php`
   - `tests/Feature/ExampleTest.php` et `tests/Unit/ExampleTest.php` (tests d'exemple Laravel)
3. Dans votre `.env` : retirez `PAYGATE_AUTH_TOKEN` / `PAYGATE_BASE_URL` et vérifiez `SUPPORT_WHATSAPP=+228XXXXXXXX` (numéro qui reçoit les commandes).
   Facultatif : `ORDER_HOLD_HOURS=24`, `ADMIN_NOTIFY_EMAIL`, `PAYMENT_FLOOZ_NUMBER`, `PAYMENT_TMONEY_NUMBER`, `PAYMENT_ACCOUNT_NAME`.
4. `php artisan migrate` (ajoute les colonnes de commande WhatsApp et la table des affiches ; les commandes existantes sont conservées).
5. `npm run build`, puis `php artisan view:clear`, puis `php artisan test` : 27 tests doivent passer.
6. **Ré-envoyez l'affiche du spectacle** depuis Admin > Spectacle (elle est désormais stockée en base).

## Le parcours
Client : choisit ses billets → nom + numéro WhatsApp (email et message facultatifs) → commande **enregistrée tout de suite** → page de suivi avec le bouton « Envoyer ma commande sur WhatsApp » (message déjà rédigé).
Admin : la commande apparaît dans **Admin > Commandes** (pastille = nombre à traiter) → « Écrire au client » → le client paie en Mobile Money → « Confirmer le paiement » (Flooz / T-Money / espèces) → billets générés → « Envoyer les billets sur WhatsApp ».
Places gardées 24 h ; « Prolonger » et « Annuler » disponibles.

## Adaptation à l'hébergement gratuit Render
- Affiche stockée en base (table `event_posters`), servie par `/events/{id}/affiche`.
- QR codes générés à la volée (plus aucun fichier écrit sur le disque).
- Places des commandes expirées libérées à l'ouverture du site / de l'admin et à chaque nouvelle commande (plus besoin de planificateur).
- Un email qui échoue ne bloque jamais une commande ni une confirmation de paiement.
- `php artisan admin:create` : crée le premier admin depuis `ADMIN_EMAIL` / `ADMIN_PASSWORD`, sans jamais écraser un mot de passe.
- `docker/start-web.sh` lance les migrations et la création de l'admin au démarrage (pas de Pre-Deploy en gratuit).
- Guide de déploiement : `DEPLOIEMENT-RENDER.md`.
