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

Importer `database/upgrade_order_contacts.sql` pour conserver la trace du moyen de contact et du motif quand l’équipe modifie ou annule une commande.

## Compétences mises en œuvre

- Catalogue de menus alimenté par MySQL.
- Filtres dynamiques en JavaScript avec une API PHP.
- Requêtes préparées PDO.
- Structure HTML sémantique et interface responsive Bootstrap.
- Modèle relationnel avec tables de liaison pour les plats et leurs allergènes (voir `docs/SCHEMA_RELATIONNEL.md`).
- Tableau de bord administratif relié à MongoDB Atlas pour les statistiques des commandes.

## Mise en ligne

La démonstration en ligne utilise Render (site PHP), Aiven Free (MySQL) et MongoDB Atlas Free (statistiques). Le `Dockerfile` prépare PHP pour Render ; la procédure et ses limites sont dans `docs/DEPLOIEMENT_GRATUIT.md`. Aucun identifiant ne doit être ajouté au dépôt.

## Documents du projet

- `docs/ECF_CHECKLIST.md` : fonctions validées et travaux restants.
- `docs/SCHEMA_RELATIONNEL.md` : tables MySQL et leurs relations.
- `docs/DIAGRAMMES.md` : cas d'utilisation et séquence d'une commande.
- `docs/CHOIX_TECHNIQUES.md` : explication simple des technologies et de leurs limites.
- `docs/CHARTE_GRAPHIQUE.md` : couleurs, polices et composants réellement utilisés.
- `docs/GESTION_PROJET.md` : étapes effectuées, historique Git et travail restant.
- `docs/GUIDE_UTILISATEUR.md` : parcours visiteur, client, employé et administrateur.
- [Tableau de suivi GitHub Projects](https://github.com/users/Albresik/projects/1/views/1) : tableau public créé pour le suivi, mais ses cartes ne restent pas visibles après actualisation.
- [Tickets du dépôt](https://github.com/Albresik/vite-gourmand/issues) : trace consultable des tâches et des tests, utilisée en complément du tableau.
- `docs/DEPLOIEMENT_GRATUIT.md` : démarche suivie pour la mise en ligne.
