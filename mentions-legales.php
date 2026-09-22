<?php
require_once __DIR__ . '/includes/layout.php';
pageHeader('Mentions légales');
?>
<main id="contenu" class="container py-5 legal-page">
  <h1 class="h2">Mentions légales</h1>
  <p class="alert alert-info">Ce site est un projet pédagogique réalisé à partir d’une entreprise fictive. Les informations d’immatriculation et d’hébergement seront complétées avant une exploitation commerciale réelle.</p>

  <h2 class="h4 mt-4">Éditeur du site</h2>
  <p>Vite &amp; Gourmand, entreprise fictive située à Bordeaux dans le cadre de l’évaluation.</p>
  <p>Forme juridique, adresse complète, numéro d’immatriculation, téléphone et adresse e-mail professionnels : à compléter avec les informations validées par le commanditaire.</p>

  <h2 class="h4 mt-4">Hébergement</h2>
  <p>Nom, adresse et coordonnées de l’hébergeur : à compléter après le choix définitif de la plateforme de déploiement.</p>

  <h2 class="h4 mt-4">Données personnelles</h2>
  <p>Les informations des formulaires servent à gérer les comptes, les commandes et les demandes de contact. Elles sont conservées dans la base de données de l’application. Pour une demande concernant vos données, utilisez la <a href="contact.php">page de contact</a>.</p>
  <p>La durée de conservation, l’identité du responsable du traitement et les coordonnées de contact dédiées doivent être définies avant une mise en service réelle.</p>
</main>
<?php pageFooter(); ?>
