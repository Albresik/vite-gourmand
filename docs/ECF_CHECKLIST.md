# Checklist ECF — Vite & Gourmand

Cette checklist décrit le périmètre minimal à livrer. Les éléments marqués **à faire** doivent être réalisés ou explicitement documentés comme limites du MVP.

## Application publique

- [x] Accueil : présentation, équipe et avis visibles.
- [x] Navigation : accueil, menus, connexion et contact (lien à créer).
- [x] Pied de page : horaires et liens vers les pages mentions légales et CGV.
- [ ] Compléter les informations légales réelles avant toute exploitation commerciale.
- [x] Catalogue de menus avec filtres dynamiques : prix, thème, régime et nombre de personnes.
- [x] Détail d’un menu et bouton de commande.
- [x] Création de compte avec validation du mot de passe.
- [x] Connexion avec mots de passe hachés.
- [ ] Réinitialisation du mot de passe.
- [x] Page de contact avec enregistrement en base.
- [ ] Envoi d’un e-mail de contact à l’entreprise (configuration SMTP à prévoir).

## Commandes et espace utilisateur

- [x] Formulaire de commande et informations de livraison.
- [x] Règles de prix : minimum, livraison et remise.
- [x] Enregistrement de la commande et statut initial.
- [x] Espace utilisateur : liste simple des commandes.
- [ ] Annulation ou modification avant acceptation.
- [ ] Historique détaillé des statuts.
- [ ] Avis client possible sur une commande terminée.

## Espaces employé et administrateur

- [ ] Contrôle des rôles utilisateur / employé / administrateur (codé, test à faire).
- [ ] Employé : gestion simple des menus et des commandes (codée, test à faire).
- [ ] Employé : validation/refus des avis.
- [ ] Administrateur : création et désactivation d’un compte employé (codées, test à faire).
- [ ] Administrateur : tableau de bord et chiffre d’affaires filtrable.

## Données, sécurité et accessibilité

- [x] MySQL : utilisateurs, menus, commandes et historique des statuts.
- [ ] MongoDB : événements/statistiques de commande.
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
