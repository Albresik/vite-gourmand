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

## Compétences mises en œuvre

- Catalogue de menus alimenté par MySQL.
- Filtres dynamiques en JavaScript avec une API PHP.
- Requêtes préparées PDO.
- Structure HTML sémantique et interface responsive Bootstrap.
