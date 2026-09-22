<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
$reviewQuery = database()->prepare('SELECT rating, comment FROM reviews WHERE status = :status ORDER BY id DESC LIMIT 3');
$reviewQuery->execute(['status' => 'approved']);
$reviews = $reviewQuery->fetchAll();
pageHeader('Accueil');
?>
<main id="contenu">
  <section class="hero py-5"><div class="container py-lg-5"><div class="row align-items-center g-4"><div class="col-lg-7"><p class="eyebrow">Traiteur à Bordeaux depuis 2001</p><h1>Des menus généreux pour vos plus beaux moments.</h1><p class="lead">Julie et José imaginent des prestations gourmandes, préparées avec soin pour vos repas, fêtes et événements.</p><a class="btn btn-primary btn-lg" href="#menus">Découvrir les menus</a></div><div class="col-lg-5"><div class="hero-card"><p class="mb-1">Une cuisine sur mesure</p><strong>25 ans de savoir-faire</strong><p class="small mt-2 mb-0">Livraison à Bordeaux et alentours.</p></div></div></div></div></section>
  <section id="menus" class="container py-5"><div class="d-flex justify-content-between align-items-end gap-3 flex-wrap"><div><p class="eyebrow">Notre carte</p><h2>Choisissez votre menu</h2></div><p id="result-count" class="text-secondary mb-2" aria-live="polite"></p></div>
    <form id="filters" class="filter-panel row g-3 my-4" aria-label="Filtrer les menus"><div class="col-sm-6 col-lg"><label class="form-label" for="max_price">Prix maximum</label><input class="form-control" id="max_price" name="max_price" type="number" min="0" placeholder="Ex. 40"></div><div class="col-sm-6 col-lg"><label class="form-label" for="min_price">Prix minimum</label><input class="form-control" id="min_price" name="min_price" type="number" min="0" placeholder="Ex. 20"></div><div class="col-sm-6 col-lg"><label class="form-label" for="theme">Thème</label><select class="form-select" id="theme" name="theme"><option value="">Tous</option><option>Noël</option><option>Pâques</option><option>Classique</option><option>Événement</option></select></div><div class="col-sm-6 col-lg"><label class="form-label" for="diet">Régime</label><select class="form-select" id="diet" name="diet"><option value="">Tous</option><option>Classique</option><option>Végétarien</option><option>Vegan</option></select></div><div class="col-sm-6 col-lg"><label class="form-label" for="people">Personnes min.</label><input class="form-control" id="people" name="people" type="number" min="1" placeholder="Ex. 6"></div></form>
    <div id="menu-list" class="row g-4" aria-live="polite"></div>
  </section>
  <section class="container pb-5">
    <p class="eyebrow">Ils nous font confiance</p>
    <h2>Des avis vérifiés</h2>
    <?php if (!$reviews): ?><p>Aucun avis validé pour le moment.</p><?php endif; ?>
    <div class="row g-4 mt-1">
      <?php foreach ($reviews as $review): ?>
        <div class="col-md-4"><blockquote class="review"><p><?= nl2br(htmlspecialchars($review['comment'])) ?></p><footer>Client ayant commandé · <?= (int)$review['rating'] ?> / 5</footer></blockquote></div>
      <?php endforeach; ?>
    </div>
  </section>
</main><?php pageFooter(); ?>
