# Gestion de projet — Vite & Gourmand

## Objectif et méthode

Le but était de réaliser une application de démonstration répondant au sujet ECF : présenter les menus d'un traiteur, permettre les commandes et leur suivi, donner un espace à l'équipe et fournir des statistiques à l'administrateur. Le projet a été mené par une seule personne, en avançant par petites fonctionnalités et en testant progressivement dans le navigateur.

Ce document distingue le suivi **réellement effectué** de l'organisation ajoutée **pour la suite**. Il ne prétend pas qu'un tableau Kanban était utilisé dès le premier jour.

## Étapes réalisées

| Étape | Travail effectué | Trace vérifiable |
| --- | --- | --- |
| Préparation | Lecture du sujet, puis trois wireframes et trois mockups dans Figma. | Exports des maquettes à joindre au dossier. |
| Base de données | Création du schéma MySQL, des tables et des données d'exemple. | `database/schema.sql` et `database/upgrade_*.sql`. |
| Application | Pages publiques, inscription, commandes, espace client, espace employé et administration. | Commits Git des 22 et 23 septembre 2026. |
| Mise en ligne | Déploiement sur Render avec MySQL Aiven et MongoDB Atlas. | Site public et procédure `docs/DEPLOIEMENT_GRATUIT.md`. |
| Vérification | Commandes fictives, statistiques et filtre par menu testés en ligne. | Checklist et captures de test. |

Les dates ci-dessus viennent de l'historique Git. La date exacte de chaque maquette n'est pas renseignée : il ne faut pas l'inventer dans le dossier.

## Utilisation de Git

Le dépôt contient une branche principale `main`, une branche `develop` et deux branches de fonctionnalité : `feature/menu-content` et `feature/order-management`. L'historique montre un regroupement du travail sur les menus et des corrections après déploiement. Toutes les modifications n'ont pas suivi parfaitement le processus prévu dans le sujet (une branche de fonctionnalité pour chaque changement, puis tests et fusion vers `develop`, puis vers `main`). Il faut présenter cela comme une amélioration à apporter, pas comme une pratique déjà entièrement appliquée.

Quelques exemples visibles dans l'historique :

- 22 septembre : initialisation du projet, contact, espaces et premiers menus/commandes.
- 23 septembre : préparation du déploiement, puis corrections après les essais en ligne (requête MySQL, erreurs PHP, tableau de bord et connexion MongoDB).

## Tableau de suivi

Lien du tableau GitHub Projects : [Vite & Gourmand — suivi ECF](https://github.com/users/Albresik/projects/1/views/1). Le tableau a été rendu public et son accès a été vérifié en navigation privée. Cependant, les cartes ajoutées disparaissent de ses vues après actualisation, même lorsqu'une issue reste liée au projet avec le statut « Done ». Le problème n'est pas résolu. Les [tickets du dépôt](https://github.com/Albresik/vite-gourmand/issues) conservent une trace consultable des tâches réalisées. Le tableau vide est une limite du livrable, et les tickets sont présentés en complément, sans prétendre qu'ils remplacent son affichage.

Le suivi prévu utilise trois colonnes : **À faire**, **En cours**, **Terminé**. Les tâches déjà terminées sont un récapitulatif reconstitué à partir des tests et de l'historique Git, pas la preuve que le tableau était utilisé dès le début.

| Tâche récapitulée | État actuel | Critère de fin |
| --- | --- | --- |
| Tester les filtres par dates des statistiques | Terminé | Une période incluant les commandes les affiche ; une période hors de ces dates n'affiche rien. |
| Tester la modification d'une commande en attente | Terminé | Les changements autorisés sont enregistrés ; le menu d'une commande ne change pas. |
| Tester la modification du profil | Terminé | Les nouvelles informations sont enregistrées après vérification du mot de passe actuel. |
| Tester le changement du mot de passe | Terminé | L'ancien mot de passe ne fonctionne plus après le changement. |
| Tester l'envoi d'un avis client | Terminé | L'avis est enregistré, mais n'apparaît pas encore sur l'accueil. |
| Tester la validation d'un avis par un administrateur | Terminé | L'avis apparaît sur l'accueil seulement après validation. |
| Tester le refus d'un avis | Terminé | Un avis refusé n'apparaît pas sur l'accueil. |
| Tester les droits d'accès | Terminé | Le client n'accède ni aux pages employé ni à l'administration ; l'employé accède à son espace mais pas à l'administration. |
| Tester la désactivation d'un employé fictif | Terminé | Une fois désactivé, son compte ne peut plus se connecter. |
| Tester les autres actions employé et administrateur | À faire | Menus, plats, horaires et comptes employés sont vérifiés avec les bons rôles. |
| Vérifier le site sur mobile et au clavier | À faire | Navigation utilisable, champs lisibles et ordre de tabulation logique. |
| Finaliser le guide utilisateur et les exports PDF | En cours | Charte et guide PDF créés ; accès de démonstration et autres documents à finaliser. |
| Compléter les informations légales réelles | À faire | Coordonnées et mentions validées avant toute exploitation commerciale. |

Les e-mails automatiques et la réinitialisation du mot de passe sont des **limites connues**, listées dans `docs/ECF_CHECKLIST.md`. Si le temps manque, il vaut mieux les annoncer clairement que les présenter comme terminés.

## Difficultés rencontrées et décisions

- **Environnement local** : Git et XAMPP ont nécessité des réglages avant les premiers tests.
- **Déploiement** : la connexion à MySQL distant a nécessité un certificat et des variables d'environnement ; plusieurs erreurs vues en ligne ont été corrigées par de nouveaux commits.
- **Deux bases de données** : MySQL reste la source des commandes, MongoDB contient seulement les données utiles au tableau de bord.
- **Tableau de projet** : les issues existent dans le dépôt, mais les vues du projet restent vides après rechargement, même après réassociation d'une issue. Une carte brouillon créée dans le tableau ne reste pas visible non plus. Pour le rendu, le lien public du tableau est fourni avec celui des tickets du dépôt et cette limite est annoncée explicitement.
- **Envoi d'e-mails** : non réalisé dans le temps disponible ; cela demanderait de configurer un service d'envoi externe et de tester les notifications.

## Présentation possible à l'oral

« J'ai commencé par les maquettes et la base de données, puis j'ai construit les parcours principaux. J'ai utilisé Git pour garder les étapes du développement. Les essais en ligne ont révélé plusieurs problèmes que j'ai corrigés. Je n'ai pas suivi parfaitement le processus de branches demandé ; je l'ai noté comme point d'amélioration. Le tableau de suivi a été ajouté pour organiser les vérifications et documents restants, pas pour faire croire qu'il était présent dès le début. »
