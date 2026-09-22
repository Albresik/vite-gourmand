# Schéma relationnel — version actuelle

Ce schéma représente les tables réellement créées par les fichiers SQL du projet. Il sera complété quand la gestion des plats, des allergènes et des avis sera ajoutée.

```mermaid
erDiagram
    users ||--o{ customer_orders : passe
    menus ||--o{ customer_orders : concerne
    customer_orders ||--o{ order_status_history : possede

    users {
        int id PK
        varchar first_name
        varchar last_name
        varchar email UK
        varchar password_hash
        enum role
        boolean is_active
    }
    menus {
        int id PK
        varchar title
        text description
        enum theme
        enum diet
        int min_people
        decimal price
        int stock
    }
    customer_orders {
        int id PK
        int user_id FK
        int menu_id FK
        int quantity
        date delivery_date
        decimal menu_total
        decimal discount_amount
        decimal delivery_fee
        decimal total_amount
        enum status
    }
    order_status_history {
        int id PK
        int order_id FK
        enum status
        timestamp created_at
    }
    contact_messages {
        int id PK
        varchar title
        varchar email
        text message
        boolean email_sent
    }
```

## Lecture simple

- Un utilisateur peut passer plusieurs commandes ; une commande appartient à un seul utilisateur.
- Un menu peut apparaître dans plusieurs commandes ; une commande concerne un seul menu.
- Une commande peut avoir plusieurs lignes d’historique ; chaque ligne correspond à un changement de statut.
- Un message de contact peut être envoyé sans compte. Il n’a donc pas de lien obligatoire avec `users`.

`PK` désigne la clé primaire, `FK` la clé étrangère et `UK` une valeur unique.

## Choix de conception

Le prix du menu, la remise et la livraison sont copiés dans la commande au moment de sa création. Si le prix d’un menu change plus tard, l’ancien montant de la commande reste compréhensible.
