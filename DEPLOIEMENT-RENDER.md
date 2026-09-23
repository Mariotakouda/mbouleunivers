# Déployer Univers 2 M'boulè sur Render

## Ce que j'ai vérifié, et ce que je n'ai pas pu tester

Mon environnement de travail n'a pas accès à Docker Hub ni à Render : je n'ai donc pas pu
construire l'image Docker ni faire un vrai déploiement. Ce que j'ai fait à la place :

- J'ai installé PostgreSQL en local et lancé `php artisan migrate:fresh --seed` dessus :
  toutes les migrations passent sans erreur (les `enum` posent parfois problème sur
  Postgres, ce n'est pas le cas ici).
- J'ai testé, sur cette base Postgres et dans un vrai navigateur : la connexion admin, la
  création d'un spectacle et d'une catégorie de billets, une réservation, le paiement
  PayGateGlobal (webhook + génération des billets), la connexion agent.

Donc le CODE de l'application fonctionne avec Postgres. Ce que je n'ai PAS pu vérifier :
la construction de l'image Docker elle-même, ni son comportement réel une fois déployée
sur Render (droits sur le disque persistant, démarrage de Nginx/PHP-FPM). Testez d'abord
sur un environnement de test Render avant de couper votre hébergement actuel.

## Coût

Ce blueprint utilise le plan **Starter** (le plan gratuit efface le disque à chaque
redéploiement et supprime la base au bout de 30 jours — inutilisable pour un site qui
encaisse de vrais paiements). Trois services payants sont créés :
- le site (web)
- la file d'attente + planificateur (worker) — envoie les emails de billets et libère les
  places non payées après 10 minutes
- la base de données PostgreSQL

Vérifiez le tarif actuel de chaque plan Starter sur render.com/pricing avant de déployer.

## Étapes

1. **Poussez ce projet sur GitHub** (ou GitLab), si ce n'est pas déjà fait. Render déploie
   à partir d'un dépôt Git.

2. Sur [render.com](https://render.com) : **New > Blueprint**, puis sélectionnez votre
   dépôt. Render lit le fichier `render.yaml` à la racine et propose de créer les 3
   services automatiquement.

3. Render vous demande de renseigner les variables marquées `sync: false` avant de créer
   les services :
   - `PAYGATE_AUTH_TOKEN` : votre clé API PayGateGlobal
   - `SUPPORT_WHATSAPP` : votre numéro WhatsApp, format `+22890000000`
   - `MAIL_HOST`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_FROM_ADDRESS` : les identifiants
     de votre service d'envoi d'emails (ex. Brevo, Mailgun, ou un compte SMTP classique)

4. Une fois les deux services (`univermboule` et `univermboule-worker`) déployés,
   notez l'adresse donnée par Render, du type `https://univermboule.onrender.com`.
   Si elle diffère de celle écrite dans `render.yaml` (par exemple parce que le nom
   était déjà pris), mettez à jour la variable `APP_URL` dans le service `univermboule`
   avec la vraie adresse, puis redéployez.

5. **Chez PayGateGlobal / BHK Konsulting** : indiquez l'URL de confirmation de paiement :
   ```
   https://votre-adresse.onrender.com/payment/webhook
   ```

6. Connectez-vous sur `https://votre-adresse.onrender.com/admin/login` avec
   `admin@universmboule.tg` / `password`, **et changez ce mot de passe immédiatement**
   (Admin > Utilisateurs).

7. Faites un vrai paiement Flooz ou T-Money, petit montant, pour vérifier que le webhook
   fonctionne et que le billet est bien généré et envoyé par email.

## Si quelque chose ne marche pas

- **Page d'erreur au premier chargement** : ouvrez les logs du service `univermboule`
  dans Render. La cause la plus probable est une variable d'environnement manquante.
- **Les billets ne sont jamais envoyés par email** : vérifiez que le service
  `univermboule-worker` est bien "Live" dans Render (c'est lui qui envoie les emails).
- **Erreur de permission sur les affiches/QR codes** : le disque persistant vient peut-être
  d'être créé avec les mauvais droits. Rouvrez un "Shell" sur le service `univermboule`
  dans Render et lancez : `chown -R application:application storage`.
