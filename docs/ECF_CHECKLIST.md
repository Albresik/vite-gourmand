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
- [ ] Envoi d’un e-mail de contact à l’entreprise (configuration SMTP à prévoir).

## Commandes et espace utilisateur

- [x] Formulaire de commande et informations de livraison.
- [x] Règles de prix : minimum, livraison et remise.
- [x] Enregistrement de la commande et statut initial.
- [x] Espace utilisateur : liste et suivi des commandes.
- [x] Annulation avant acceptation et historique des statuts (testés dans le navigateur).
- [ ] Modification avant acceptation, sans changer le menu (codée, test manuel à faire).
- [ ] Modification du profil (codée, test manuel à faire).
- [ ] Avis client sur commande terminée (codé, test manuel à faire).

## Espaces employé et administrateur

- [ ] Contrôle des rôles utilisateur / employé / administrateur (codé, test à faire).
- [ ] Employé : gestion des menus, plats, images et commandes (codée, tests manuels à faire).
- [ ] Employé : modification des commandes après contact client (codée, test manuel à faire).
- [ ] Employé : validation/refus des avis (codés, test manuel à faire).
- [ ] Employé : modification des horaires (codée, test manuel à faire).
- [ ] Administrateur : création et désactivation d’un compte employé (codées, test à faire).
- [ ] Administrateur : tableau de bord MongoDB et chiffre d’affaires filtrable (codés, Atlas et test en ligne à faire).

## Données, sécurité et accessibilité

- [x] MySQL : utilisateurs, menus, plats, allergènes, images, commandes et historique des statuts.
- [ ] MongoDB : copie anonymisée des commandes pour les statistiques (codée, connexion Atlas à faire).
- [x] PDO et requêtes préparées.
- [x] Hachage des mots de passe et sessions.
- [ ] Validation serveur sur les autres formulaires.
- [ ] Vérification de l’accessibilité : titres, labels, contraste et navigation clavier.

## Livrables

- [ ] Dépôt Git public avec README, branches et historique de commits.
- [ ] Fichier SQL initial et données de démonstration.
- [ ] Application déployée avec URL publique.
- [x] Trois wireframes et trois mockups exportés depuis Figma.
- [ ] Charte graphique et police utilisées.
- [ ] Guide utilisateur avec comptes de démonstration.
- [ ] Documentation de gestion de projet.
- [ ] Documentation technique : choix, environnement, MCD, diagrammes et déploiement (schéma relationnel commencé).
