# Schéma relationnel — version actuelle

Ce schéma représente les tables créées par les fichiers SQL du projet.

```mermaid
erDiagram
    users ||--o{ customer_orders : passe
    menus ||--o{ customer_orders : concerne
    customer_orders ||--o{ order_status_history : possede
    menus ||--o{ menu_dishes : contient
    dishes ||--o{ menu_dishes : apparait_dans
    dishes ||--o{ dish_allergens : declare
    allergens ||--o{ dish_allergens : concerne
    menus ||--o{ menu_images : illustre
    customer_orders ||--o| reviews : donne_lieu_a
    customer_orders ||--o{ order_contact_logs : contacts
    users ||--o{ order_contact_logs : employe

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
        varchar conditions
        int lead_days
    }
    dishes {
        int id PK
        varchar name
        varchar description
        enum course
    }
    menu_dishes {
        int menu_id PK, FK
        int dish_id PK, FK
    }
    allergens {
        int id PK
        varchar name UK
    }
    dish_allergens {
        int dish_id PK, FK
        int allergen_id PK, FK
    }
    menu_images {
        int id PK
        int menu_id FK
        varchar image_url
        varchar alt_text
    }
    reviews {
        int id PK
        int order_id FK, UK
        int rating
        varchar comment
        enum status
    }
    opening_hours {
        int day_of_week PK
        time opening_time
        time closing_time
        boolean is_closed
    }
    order_contact_logs {
        int id PK
        int order_id FK
        int employee_id FK
        enum contact_method
        varchar note
        timestamp created_at
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
- Un menu comporte plusieurs plats, et un plat peut figurer dans plusieurs menus : `menu_dishes` est la table de liaison.
- Un plat peut déclarer plusieurs allergènes, et un allergène peut concerner plusieurs plats : `dish_allergens` est la seconde table de liaison.
- Un menu peut avoir plusieurs images. La première est l’image principale.
- Une commande terminée peut recevoir au plus un avis. Son statut `pending`, `approved` ou `rejected` décide de sa visibilité publique.
- `opening_hours` contient sept lignes indépendantes, une par jour de la semaine.
- Chaque modification ou annulation faite par l’équipe conserve une trace du contact client dans `order_contact_logs`.
- Une commande peut avoir plusieurs lignes d’historique ; chaque ligne correspond à un changement de statut.
- Un message de contact peut être envoyé sans compte. Il n’a donc pas de lien obligatoire avec `users`.

`PK` désigne la clé primaire, `FK` la clé étrangère et `UK` une valeur unique.

## Choix de conception

Le prix du menu, la remise et la livraison sont copiés dans la commande au moment de sa création. Si le prix d’un menu change plus tard, l’ancien montant de la commande reste compréhensible.
