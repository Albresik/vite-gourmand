# Vite & Gourmand

## Lancer le projet en local

1. Copier le dossier `vite-gourmand` dans `C:\xampp\htdocs\`.
2. Démarrer **Apache** et **MySQL** dans le panneau XAMPP.
3. Ouvrir phpMyAdmin : `http://localhost/phpmyadmin`.
4. Utiliser l’onglet **Importer**, sélectionner `database/schema.sql`, puis lancer l’import.
5. Ouvrir `http://localhost/vite-gourmand/`.

Après le premier import, importer aussi `database/upgrade_auth.sql` afin d’ajouter la table des comptes.

Puis importer `database/upgrade_orders.sql` pour enregistrer les commandes et leur historique de statut.

Importer `database/upgrade_contact.sql` pour activer le formulaire de contact.

Importer `database/upgrade_employee.sql` pour ajouter les informations d’annulation. Pour créer le premier administrateur, lancer en local `C:\xampp\php\php.exe scripts\create_admin.php` depuis le dossier du projet. Aucun administrateur ne peut être créé depuis le site public.

Importer enfin `database/upgrade_menu_content.sql` pour ajouter les plats, allergènes, images et conditions des menus. Chaque fichier `upgrade_*.sql` s’importe **une seule fois**, dans l’ordre ci-dessus. Si la base existe déjà et contient ces tables, ne pas réimporter le fichier.

Importer ensuite `database/upgrade_reviews.sql` pour activer les avis clients liés aux commandes terminées. Les avis ne sont affichés sur l’accueil qu’après validation par un employé ou administrateur.

Importer enfin `database/upgrade_hours.sql` pour afficher les horaires du lundi au dimanche dans le pied de page. Les horaires fournis sont des exemples et se modifient depuis l’espace employé.

## Compétences mises en œuvre

- Catalogue de menus alimenté par MySQL.
- Filtres dynamiques en JavaScript avec une API PHP.
- Requêtes préparées PDO.
- Structure HTML sémantique et interface responsive Bootstrap.
- Modèle relationnel avec tables de liaison pour les plats et leurs allergènes (voir `docs/SCHEMA_RELATIONNEL.md`).
