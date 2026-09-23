# Guide utilisateur — Vite & Gourmand

Ce guide décrit les parcours disponibles dans l'application de démonstration. Il faut utiliser des comptes et des données **fictifs** pour les essais publics.

## 1. Visiteur

Depuis l'accueil, le visiteur peut lire la présentation du traiteur, voir les horaires et consulter les menus. Les filtres permettent de chercher par prix, thème, régime et nombre minimum de personnes. Le bouton **Détails** ouvre la description du menu, ses plats, allergènes, conditions et son prix.

La page **Contact** permet de saisir un objet, une adresse e-mail et un message. Le message est enregistré dans la base du site ; il n'est **pas envoyé par e-mail** dans cette version.

## 2. Client

### Créer un compte et commander

1. Cliquer sur **Connexion**, puis **Créer un compte**. Renseigner le nom, le prénom, l'e-mail, le téléphone, l'adresse et un mot de passe conforme aux indications du formulaire.
2. Se connecter, revenir aux menus et choisir **Détails** puis **Commander**.
3. Sur le formulaire, renseigner le lieu, la date, l'heure, la distance hors Bordeaux si nécessaire et le nombre de personnes. Respecter le minimum indiqué par le menu et le délai de commande affiché.
4. Vérifier le récapitulatif avant de cliquer sur **Confirmer la commande**. La page de confirmation affiche le numéro et le total. Aucun e-mail de confirmation n'est envoyé dans cette version.

### Suivre ou modifier une commande

Dans **Mon espace**, la section **Mes commandes** affiche les commandes et leurs statuts. Le lien **Voir le suivi de la commande** montre les étapes et leurs dates.

Tant qu'une commande est **En attente**, le client peut cliquer sur **Modifier ma commande** pour changer les informations autorisées, ou **Annuler ma commande**. Le menu choisi ne peut pas être remplacé. Une fois la commande acceptée, ces actions ne sont plus proposées dans l'espace client.

### Profil et avis

Le bouton **Modifier mes informations** ouvre le profil. Le mot de passe actuel est demandé pour enregistrer les changements. Le bouton **Changer mon mot de passe** permet de choisir un nouveau mot de passe en fournissant l'ancien et en confirmant le nouveau.

Quand une commande est **Terminée**, le bouton **Donner mon avis** apparaît. Le client choisit une note de 1 à 5 et écrit un commentaire. L'avis n'est visible sur l'accueil qu'après validation par l'équipe.

## 3. Employé

Après connexion avec un compte employé, **Mon espace** ouvre la gestion des commandes. L'employé peut filtrer les commandes, consulter leurs informations et faire avancer leur statut. Pour annuler une commande, il doit indiquer le contact préalable du client et le motif.

Les boutons de gestion donnent accès à :

- **Gérer les menus** : créer, modifier ou retirer un menu du catalogue ; accéder aussi à ses images.
- **Gérer les plats** : créer ou modifier les plats et leurs allergènes.
- **Valider les avis** : approuver ou refuser les avis en attente.
- **Gérer les horaires** : modifier les heures affichées dans le pied de page.

Un employé peut également modifier certaines informations d'une commande après avoir contacté le client. Il doit conserver une trace du moyen de contact et de la raison du changement.

## 4. Administrateur

Après connexion avec un compte administrateur, **Mon espace** ouvre l'administration. L'administrateur peut créer un compte employé, le désactiver ou le réactiver. Il peut aussi ouvrir la gestion des commandes et menus.

Le bouton **Voir le tableau de bord** affiche le nombre de commandes par menu et le chiffre d'affaires des commandes terminées. Les commandes annulées sont exclues du graphique. Les filtres permettent de limiter les résultats à un menu ou à une période. La période porte sur la **date d'enregistrement** de la commande, pas sur sa date de prestation.

## 5. Comptes de démonstration à préparer pour le jury

Les identifiants réels ne doivent **pas** figurer dans ce fichier ni dans le dépôt Git public. Prévoir des comptes dédiés à la démonstration, différents des comptes personnels, puis communiquer les accès au jury par un canal privé conformément aux consignes de l'évaluation.

| Rôle | Compte à préparer | Parcours à montrer |
| --- | --- | --- |
| Client | Compte fictif avec une commande de test | Commande, suivi, profil, avis après commande terminée. |
| Employé | Compte de démonstration créé par l'administrateur | Statuts, menus, plats, horaires, avis. |
| Administrateur | Compte de démonstration distinct du compte personnel | Gestion des employés et statistiques. |

Avant de donner ces accès, vérifier que les comptes et les commandes de test ne contiennent aucune donnée personnelle. Changer ou désactiver les accès de démonstration après l'évaluation.

## 6. Limites de la version présentée

- Les e-mails automatiques de confirmation et de fin de commande ne sont pas implémentés.
- Le formulaire de contact enregistre les messages, mais ne les expédie pas par e-mail.
- La réinitialisation du mot de passe par le client n'est pas disponible.
- Les mentions légales de démonstration ne remplacent pas les informations réelles nécessaires à une exploitation commerciale.
