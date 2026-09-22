<?php
require_once __DIR__ . '/includes/layout.php';
pageHeader('Conditions générales de vente');
?>
<main id="contenu" class="container py-5 legal-page">
  <h1 class="h2">Conditions générales de vente</h1>
  <p class="alert alert-info">Version pédagogique : ces conditions décrivent le fonctionnement de l’application. Elles devront être validées et complétées par l’entreprise avant une vente réelle.</p>

  <h2 class="h4 mt-4">Menus et prix</h2>
  <p>Chaque fiche indique le prix par personne et le nombre minimal de convives. Le récapitulatif affiche le prix des menus, la remise éventuelle, les frais de livraison et le total avant confirmation.</p>

  <h2 class="h4 mt-4">Remise et livraison</h2>
  <p>Une remise de 10 % s’applique au prix des menus lorsque la commande comprend au moins cinq personnes de plus que le minimum indiqué. La livraison est facturée 5 € à Bordeaux. Hors Bordeaux, le calcul utilisé par cette démonstration est de 5 € plus 0,59 € par kilomètre renseigné.</p>

  <h2 class="h4 mt-4">Conditions propres à chaque menu</h2>
  <p>Les délais de commande, les allergènes et les autres conditions particulières sont à consulter sur la fiche du menu avant de commander.</p>

  <h2 class="h4 mt-4">Suivi et annulation</h2>
  <p>Une commande enregistrée commence avec le statut « en attente ». Le client peut consulter son suivi dans son espace personnel. Les modalités définitives de paiement, d’annulation, de réclamation et de restitution du matériel doivent être validées par l’entreprise.</p>
</main>
<?php pageFooter(); ?>
