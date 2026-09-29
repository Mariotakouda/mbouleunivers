# Commande via WhatsApp — mise à jour d'Univers 2 M'boulè

## Installer
1. Copiez le contenu de ce dossier PAR-DESSUS votre projet (mêmes chemins), en gardant votre `.env`, `.git`, `vendor` et `node_modules`.
2. **Supprimez** ces fichiers devenus inutiles (ancien paiement en ligne) :
   - `app/Services/PayGateService.php`
   - `app/Http/Controllers/Public/PaymentController.php`
   - `resources/views/public/payment-show.blade.php`
   - `resources/views/public/payment-success.blade.php`
   - `resources/views/public/payment-failed.blade.php`
   - `tests/Feature/ExampleTest.php` et `tests/Unit/ExampleTest.php` (tests d'exemple Laravel, non utilisés)
3. Dans votre `.env`, vous pouvez retirer `PAYGATE_AUTH_TOKEN` et `PAYGATE_BASE_URL`, et vérifier :
   - `SUPPORT_WHATSAPP=+228XXXXXXXX` : le numéro qui reçoit les commandes (obligatoire pour le bouton WhatsApp)
   - `ORDER_HOLD_HOURS=24` (facultatif) : durée pendant laquelle les places sont gardées
   - `ADMIN_NOTIFY_EMAIL=` (facultatif) : email prévenu à chaque commande
4. `php artisan migrate` (ajoute 3 colonnes à `orders` et rend l'email facultatif ; les commandes existantes sont conservées).
5. `npm run build` (de nouveaux styles sont utilisés), puis `php artisan test` : 17 tests doivent passer.
6. Sur Render : retirez `PAYGATE_*` et ajoutez `ADMIN_NOTIFY_EMAIL` si voulu (déjà prévu dans le nouveau `render.yaml`).

## Le parcours
Client : choisit ses billets → renseigne nom + numéro WhatsApp (email et message facultatifs) → la commande est **enregistrée tout de suite** → page de suivi avec le bouton « Envoyer ma commande sur WhatsApp » (message déjà rédigé : référence, spectacle, billets, total, coordonnées).
Admin : la commande apparaît dans **Admin > Commandes** (pastille orange = nombre à traiter) → « Écrire au client » (WhatsApp) → le client paie en Mobile Money → « Confirmer le paiement » (choix Flooz / T-Money / espèces) → billets générés → « Envoyer les billets sur WhatsApp ».
Places gardées 24 h ; « Prolonger » et « Annuler » disponibles ; libération automatique à l'expiration.
