# Choix techniques — notes pour le dossier ECF

Ce document décrit les choix réellement utilisés dans l'application. Il sert de base de travail : avant l'oral, reformuler chaque justification avec ses propres mots et être capable de montrer un exemple dans le code.

## Technologies

| Choix | Pourquoi dans ce projet ? | Limite à connaître |
| --- | --- | --- |
| HTML5 | Structurer les pages avec un en-tête, une navigation, un contenu principal et un pied de page. | La structure seule ne garantit pas l'accessibilité : il faut aussi tester au clavier. |
| CSS et Bootstrap | Adapter rapidement les formulaires et les colonnes aux écrans mobiles et aux ordinateurs. Le fichier `assets/style.css` personnalise les couleurs et l'apparence. | Bootstrap ne remplace pas un travail de mise en page ni les tests sur mobile. |
| JavaScript | Filtrer les menus sans recharger toute la page et afficher une estimation du prix de la commande. | Le navigateur peut être modifié par l'utilisateur : PHP refait les vérifications et le calcul du prix. |
| PHP | Traiter les formulaires, gérer les sessions, vérifier les rôles et accéder aux bases de données. PHP fonctionne avec XAMPP en local. | Le code PHP doit valider les données reçues et ne jamais afficher les secrets. |
| MySQL | Relier les utilisateurs, menus, commandes, plats, allergènes et historiques par des clés étrangères. | Les modifications du schéma demandent des fichiers SQL à importer dans le bon ordre. |
| MongoDB Atlas | Stocker une copie limitée des commandes pour le tableau de bord demandé dans le sujet. | Pour ce petit projet, MySQL aurait techniquement suffi ; la copie est synchronisée à l'ouverture du tableau de bord, pas en temps réel. |
| Git et GitHub | Garder l'historique du travail et fournir le dépôt public demandé. Les secrets restent hors du dépôt. | Il faut vérifier les fichiers avant chaque publication. |
| Render, Aiven et Atlas | Héberger séparément l'application PHP, MySQL et MongoDB avec leurs offres gratuites pour la démonstration. | Les offres gratuites ont des limites et ne conviennent pas à une exploitation commerciale sans réévaluation. |

## Environnement de travail

- Éditeur : Visual Studio Code.
- Développement local : XAMPP avec Apache, PHP et MySQL ; phpMyAdmin sert à voir et importer les tables.
- Maquettes : Figma, avec trois wireframes et trois mockups.
- Version du projet : PHP 8.2 dans le `Dockerfile` de la version en ligne. Les dépendances PHP sont indiquées dans `composer.json`.
- Mise en ligne : Render construit le conteneur à partir du dépôt GitHub. Aiven héberge MySQL et Atlas héberge MongoDB.

## Données et sécurité

MySQL est la source principale des commandes. Au moment de la commande, le prix des menus, la remise, la livraison et le total sont enregistrés : une modification ultérieure du prix d'un menu ne change pas une ancienne commande. Une transaction regroupe la diminution du stock, l'insertion de la commande et son premier état ; si l'une de ces étapes échoue, l'ensemble est annulé.

L'accès à la base MySQL passe par PDO et des requêtes préparées. Les mots de passe des comptes sont hachés. Les pages réservées contrôlent le rôle de l'utilisateur. Les formulaires qui changent des données utilisent un jeton CSRF. Les secrets de connexion sont placés dans les variables d'environnement de Render et non dans Git. La connexion distante à MySQL vérifie le certificat fourni par Aiven.

Pour les statistiques, MongoDB reçoit seulement l'identifiant de commande, le menu, la date, l'état et le montant. Le nom, l'adresse, le téléphone et l'e-mail du client n'y sont pas copiés. Le chiffre d'affaires du tableau de bord compte seulement les commandes terminées ; les commandes annulées n'apparaissent pas dans le graphique.

## Limites connues à annoncer honnêtement

- Les e-mails automatiques n'ont pas été réalisés dans le temps disponible. La confirmation de commande est affichée sur le site et le client peut suivre son statut dans son espace. Le formulaire de contact enregistre le message dans MySQL, mais ne l'envoie pas par e-mail.
- La réinitialisation du mot de passe n'est pas encore proposée au public.
- Les mentions légales et les coordonnées réelles sont à compléter avant un usage commercial.
- Il reste des tests manuels : filtres par dates, autres parcours employé et administrateur, mobile, clavier et contrastes.

## Trois phrases possibles à l'oral

1. « J'ai choisi PHP et MySQL parce que je pouvais les utiliser localement avec XAMPP et que les données du site sont liées entre elles. »
2. « JavaScript améliore l'affichage, mais je refais les contrôles importants en PHP car les données envoyées par le navigateur ne sont pas fiables. »
3. « MongoDB sert uniquement au tableau de bord demandé ; les commandes restent dans MySQL, et je ne copie pas les coordonnées des clients dans MongoDB. »
