# Diagrammes de l'application

Ces diagrammes décrivent les fonctions présentes dans le projet. Ils complètent le [schéma relationnel](SCHEMA_RELATIONNEL.md), qui représente les tables MySQL.

## 1. Cas d'utilisation

Un visiteur peut consulter les menus sans compte. Pour commander, il doit créer un compte ou se connecter. L'employé gère les commandes et le contenu du site. L'administrateur peut aussi gérer les comptes employés et consulter les statistiques.

```mermaid
flowchart LR
    V[Visiteur] --> M(Consulter et filtrer les menus)
    V --> C(Envoyer un message de contact)
    V --> I(Créer un compte ou se connecter)

    U[Client connecté] --> O(Passer une commande)
    U --> S(Suivre ou modifier sa commande)
    U --> P(Modifier son profil)
    U --> A(Donner un avis après la commande)

    E[Employé] --> G(Gérer les menus et les plats)
    E --> T(Traiter les commandes)
    E --> R(Valider ou refuser les avis)
    E --> H(Modifier les horaires)

    D[Administrateur] --> G
    D --> T
    D --> R
    D --> H
    D --> X(Gérer les comptes employés)
    D --> B(Consulter les statistiques)
```

À l'oral : « J'ai séparé les actions selon le rôle. Le visiteur découvre l'offre, le client passe et suit ses commandes, l'employé traite l'activité quotidienne et l'administrateur dispose en plus des comptes employés et du tableau de bord. »

## 2. Séquence : passer une commande

Ce scénario commence une fois que le client est connecté et a choisi un menu. Le calcul du prix est refait côté PHP lors de la validation : l'affichage immédiat en JavaScript ne suffit pas à sécuriser le montant.

```mermaid
sequenceDiagram
    actor Client
    participant Navigateur
    participant PHP as order.php
    participant MySQL

    Client->>Navigateur: Choisit le menu et ouvre le formulaire
    Navigateur->>PHP: Demande le formulaire avec menu_id
    PHP->>MySQL: Lit le compte et le menu disponible
    MySQL-->>PHP: Informations du client et du menu
    PHP-->>Navigateur: Affiche le formulaire prérempli
    Client->>Navigateur: Saisit date, adresse, heure et personnes
    Navigateur->>PHP: Envoie le formulaire
    PHP->>PHP: Vérifie les données et recalcule le prix
    alt Données invalides
        PHP-->>Navigateur: Affiche les erreurs à corriger
    else Données valides
        PHP->>MySQL: Démarre une transaction et diminue le stock
        PHP->>MySQL: Enregistre la commande en attente
        PHP->>MySQL: Enregistre le premier état dans l'historique
        PHP->>MySQL: Valide la transaction
        PHP-->>Navigateur: Redirige vers la confirmation
    end
```

À l'oral : « Une transaction évite de diminuer le stock si l'enregistrement de la commande échoue. La commande commence à l'état `pending`, et ce premier état est aussi conservé dans l'historique. »

## 3. Où interviennent les deux bases ?

MySQL conserve les données nécessaires au fonctionnement du site : comptes, menus, commandes et suivi. MongoDB sert au tableau de bord : lorsque l'administrateur l'ouvre, le site copie les informations utiles des commandes dans une collection `order_stats`, sans nom, adresse, téléphone ni e-mail du client. Le graphique lit ensuite cette collection. MySQL reste la source principale des commandes.

Cette séparation est un choix de projet pour répondre à la consigne d'utiliser aussi une base non relationnelle ; pour ce petit volume de données, MySQL seul aurait suffi techniquement.
