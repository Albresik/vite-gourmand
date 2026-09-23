# Checklist ECF — Vite & Gourmand

Cette checklist décrit le périmètre minimal à livrer. Les éléments marqués **à faire** doivent être réalisés ou explicitement documentés comme limites du MVP.

## Application publique

- [x] Accueil : présentation, équipe et affichage des avis approuvés (aucun faux avis de démonstration).
- [x] Navigation : accueil, menus, connexion et contact.
- [x] Pied de page : horaires du lundi au dimanche et liens vers mentions légales et CGV.
- [ ] Compléter les informations légales réelles avant toute exploitation commerciale.
- [x] Catalogue de menus avec filtres dynamiques : prix, thème, régime et nombre de personnes.
- [x] Détail d’un menu (plats, allergènes, images, conditions) et bouton de commande.
- [x] Création de compte avec validation du mot de passe.
- [x] Connexion avec mots de passe hachés.
- [ ] Réinitialisation du mot de passe.
- [x] Page de contact avec enregistrement en base.
- [ ] Envoi d’un e-mail de contact à l’entreprise : non réalisé, message seulement enregistré en base.

## Commandes et espace utilisateur

- [x] Formulaire de commande et informations de livraison.
- [x] Règles de prix : minimum, livraison et remise.
- [x] Enregistrement de la commande et statut initial.
- [ ] E-mail de confirmation et e-mail après commande terminée : non réalisés.
- [x] Espace utilisateur : liste et suivi des commandes.
- [x] Annulation avant acceptation et historique des statuts (testés dans le navigateur).
- [x] Modification avant acceptation, sans changer le menu (testée dans le navigateur).
- [x] Modification du profil (testée dans le navigateur).
- [x] Changement du mot de passe en étant connecté (testé après mise en ligne).
- [x] Avis client sur commande terminée : envoi testé, avis non visible avant validation.

## Espaces employé et administrateur

- [x] Contrôle des rôles utilisateur / employé / administrateur : accès client refusé aux pages employé et admin ; accès employé autorisé à son espace et refusé à l'administration.
- [ ] Employé : gestion des menus, plats, images et commandes (codée, tests manuels à faire).
- [ ] Employé : modification des commandes après contact client (codée, test manuel à faire).
- [x] Employé ou administrateur : validation d'un avis testée ; l'avis approuvé apparaît sur l'accueil.
- [x] Employé ou administrateur : refus d'un avis testé ; l'avis refusé ne s'affiche pas sur l'accueil.
- [ ] Employé : modification des horaires (codée, test manuel à faire).
- [x] Administrateur : création et désactivation d'un compte employé fictif testées ; connexion refusée après désactivation.
- [x] Administrateur : tableau de bord MongoDB affiché en ligne, avec deux commandes terminées et un total vérifié de 1 200,12 €.
- [x] Filtre par menu du tableau de bord testé en ligne.
- [x] Filtres par dates du tableau de bord testés en ligne : aucune commande hors période, deux commandes retrouvées sur la période de création.

## Données, sécurité et accessibilité

- [x] MySQL : utilisateurs, menus, plats, allergènes, images, commandes et historique des statuts.
- [x] MongoDB : copie limitée des commandes, sans coordonnées des clients, pour les statistiques ; connexion Atlas et affichage en ligne vérifiés.
- [x] PDO et requêtes préparées.
- [x] Hachage des mots de passe et sessions.
- [ ] Validation serveur sur les autres formulaires.
- [ ] Vérification de l’accessibilité : titres, labels, contraste et navigation clavier.

## Livrables

- [x] Dépôt Git public avec README, branches et historique de commits.
- [x] Fichier SQL initial et données de démonstration.
- [x] Application déployée avec URL publique.
- [x] Trois wireframes et trois mockups exportés depuis Figma.
- [x] Charte graphique et police utilisées : document rédigé et PDF d'une page créé puis vérifié visuellement.
- [x] Guide utilisateur : parcours rédigés et PDF de trois pages vérifié ; les accès administrateur sont communiqués séparément dans le dossier privé d'évaluation, selon l'indication de l'auteur.
- [x] Documentation de gestion de projet : tableau public accessible mais vide après actualisation ; limite expliquée et tickets du dépôt fournis comme trace complémentaire.
- [x] Documentation technique : première version des choix, de l'environnement, du schéma relationnel, des diagrammes et du déploiement rédigée. À relire et à illustrer avant le rendu final.
