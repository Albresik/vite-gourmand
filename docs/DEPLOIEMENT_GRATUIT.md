# Déploiement gratuit envisagé — Vite & Gourmand

**État : préparation locale, pas encore déployé.** Ne jamais publier de mots de passe ou de chaînes de connexion dans Git.

## Choix et limites

- **Render Free** : héberge le site PHP depuis le `Dockerfile`. Le site se met en veille après 15 minutes d'inactivité ; le premier chargement peut prendre environ une minute. Le système de fichiers est temporaire.
- **Aiven MySQL Free** : conserve utilisateurs, menus et commandes. L'offre gratuite annoncée a 1 Go de stockage et peut être mise en veille après une longue inactivité.
- **MongoDB Atlas Free** : conserve une copie sans données personnelles des commandes pour le graphique administrateur. Le cluster `ViteGourmand` est créé ; ses données d'exemple ne sont pas les commandes du site.
- L'option **Heroku + JawsDB** n'est pas retenue : JawsDB a un palier gratuit, mais le serveur PHP Heroku nécessite un dyno payant.

Ces offres conviennent à une démonstration d'évaluation, pas à un service commercial. Render limite aussi l'usage mensuel et peut suspendre un service Free en cas de trafic sortant inhabituel. Vérifier les conditions affichées dans chaque compte avant toute activation.

## 1. Préparer Atlas

1. Dans **Security > Database & Network Access**, créer un utilisateur propre à l'application avec le rôle `readWrite` limité à la base `vite_gourmand_prod`. Conserver l'utilisateur `atlasAdmin` existant, mais ne pas l'utiliser dans le site.
2. Plus tard, quand le service Render existe, relever ses plages d'IP sortantes dans **Connect > Outbound** et ajouter uniquement ces plages à la liste d'accès Atlas. Ne pas ouvrir `0.0.0.0/0` par facilité.
3. Récupérer l'URI via **Connect > Drivers**. Retirer la partie `utilisateur:<db_password>@` pour ne garder que l'adresse du cluster dans `MONGODB_URI`. Définir séparément dans Render `MONGODB_USER`, `MONGODB_PASSWORD` (mot de passe non encodé) et `MONGODB_DB=vite_gourmand_prod`. Ne jamais communiquer ces valeurs.

## 2. Préparer Aiven

1. Créer un compte et un service **MySQL Free**, puis attendre l'état *Running*.
2. Relever dans la page du service les valeurs de connexion : host, port, base (`defaultdb` au départ), utilisateur, mot de passe. Ne pas les recopier dans le dépôt ni dans un message.
3. Télécharger le certificat **CA Certificate**. Il servira à vérifier la connexion chiffrée à MySQL.
4. Sur le poste local, fournir `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD` et `DB_SSL_CA` (chemin absolu du certificat) au processus PHP, puis lancer `php scripts/import_remote.php` une seule fois sur la base vide. Le script refuse une base locale ou qui contient déjà des tables. Il importe le schéma et les données fictives des fichiers SQL, pas les commandes ou comptes personnels de XAMPP.
5. Vérifier le nombre de tables avec `php scripts/test_remote.php`, puis créer l'administrateur avec `php scripts/create_admin.php --remote` dans le même environnement de connexion. Le paramètre `--remote` refuse une base locale. Fournir un mot de passe inédit par `ADMIN_PASSWORD` dans l'environnement temporaire, différent de celui du compte local.

## 3. Préparer Render

1. Pousser la branche principale testée vers le dépôt Git public.
2. Dans Render, créer **New > Web Service**, sélectionner le dépôt, la branche principale, le langage **Docker** et le plan **Free**. Le `Dockerfile` installe PHP, l'extension MongoDB, Composer et Apache sur le port 10000.
3. Dans **Environment**, définir `DB_HOST`, `DB_PORT`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`, `DB_SSL_CA=/etc/secrets/ca.pem`, puis `MONGODB_URI` (sans identifiants), `MONGODB_USER`, `MONGODB_PASSWORD` et `MONGODB_DB=vite_gourmand_prod`.
4. Ajouter le certificat Aiven comme **Secret File** nommé `ca.pem`, disponible à `/etc/secrets/ca.pem` à l'exécution. Ne pas ajouter le certificat ni les mots de passe au dépôt.
5. Déployer, puis relever les plages **Connect > Outbound** pour autoriser Render dans Atlas.

## 4. Tester le site public

Vérifier la page d'accueil, les menus, l'inscription, la connexion, une commande **fictive**, l'espace employé et les statistiques administrateur. Vérifier aussi la navigation clavier et le mobile. Les e-mails automatiques et les mentions légales réelles restent à finaliser avant toute exploitation commerciale. Ne jamais utiliser de vraies données personnelles pour les essais publics.

## Références officielles

- [Render Free et ses limites](https://render.com/docs/free)
- [PHP avec Docker sur Render](https://render.com/docs/docker)
- [Fichiers secrets Render](https://render.com/docs/configure-environment-variables)
- [Plages IP sortantes Render](https://render.com/docs/outbound-ip-addresses)
- [MySQL Free Aiven](https://aiven.io/docs/products/mysql/concepts/mysql-free-tier)
- [Connexion PHP vers Aiven](https://aiven.io/docs/products/mysql/howto/connect-with-php)
- [Connexion au cluster Atlas](https://www.mongodb.com/docs/atlas/connect-to-database-deployment/)
