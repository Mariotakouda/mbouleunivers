# Mettre Univers 2 M'boulè en ligne gratuitement (Render + Neon)

Ce guide est pensé pour un premier déploiement, étape par étape. Rien n'est payant.

## Ce que vous devez savoir avant de commencer (plan gratuit)

- **Le site s'endort après ~15 minutes sans visite.** La première personne qui arrive ensuite attend environ une minute. C'est normal en gratuit.
- **Le disque est effacé à chaque redémarrage.** Le projet a été adapté : l'affiche est stockée dans la base de données et les QR codes sont générés à la volée. Rien ne se perd.
- **La base Postgres gratuite de Render expire au bout de 30 jours.** On utilise donc une base externe gratuite (Neon) qui n'est pas concernée.
- **Pas de tâche de fond.** Les emails sont désactivés par défaut (WhatsApp suffit) et les places des commandes non confirmées sont libérées automatiquement quand quelqu'un ouvre le site.
- Pour de vraies ventes, un plan payant reste plus confortable (pas de mise en veille). Vérifiez les tarifs actuels sur render.com/pricing.

## Les étapes

1. Mettre le projet sur GitHub.
2. Créer la base de données gratuite sur Neon et copier son adresse de connexion.
3. Créer le site sur Render (New > Blueprint) et renseigner les variables.
4. Vérifier que le site fonctionne, se connecter à l'admin, créer le spectacle et les billets.
5. Faire une commande de test de bout en bout.

## Variables à renseigner sur Render

| Variable | Valeur |
|---|---|
| `DB_URL` | l'adresse de connexion donnée par Neon |
| `ADMIN_EMAIL` | votre email d'administrateur |
| `ADMIN_PASSWORD` | un mot de passe solide (10 caractères minimum) |
| `SUPPORT_WHATSAPP` | le numéro qui reçoit les commandes, format `+22890000000` |
| `PAYMENT_FLOOZ_NUMBER`, `PAYMENT_TMONEY_NUMBER`, `PAYMENT_ACCOUNT_NAME` | facultatif : cités dans le message WhatsApp envoyé au client |

Le premier administrateur est créé automatiquement au premier démarrage avec `ADMIN_EMAIL` / `ADMIN_PASSWORD`. Le mot de passe n'est jamais réinitialisé aux démarrages suivants.

## Si quelque chose ne marche pas

- **Page d'erreur ou service qui ne démarre pas** : ouvrez l'onglet Logs du service sur Render. La cause la plus fréquente est une variable manquante ou une adresse `DB_URL` incorrecte.
- **Impossible de se connecter à l'admin** : vérifiez `ADMIN_EMAIL` et `ADMIN_PASSWORD` (10 caractères minimum), puis redéployez. Un administrateur n'est créé que s'il n'en existe aucun.
- **Le site est lent à la première visite** : il se réveille (plan gratuit).
