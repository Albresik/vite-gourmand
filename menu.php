<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
$request = database()->prepare('SELECT * FROM menus WHERE id = :id AND stock > 0');
$request->execute(['id' => $id]);
$menu = $request->fetch();

if (!$menu) {
    http_response_code(404);
    pageHeader('Menu introuvable');
    echo '<main id="contenu" class="container py-5"><h1>Menu introuvable</h1></main>';
    pageFooter();
    exit;
}

$request = database()->prepare(
    "SELECT d.id, d.name, d.description, d.course,
            GROUP_CONCAT(a.name ORDER BY a.name SEPARATOR ', ') AS allergen_names
     FROM dishes d
     JOIN menu_dishes md ON md.dish_id = d.id
     LEFT JOIN dish_allergens da ON da.dish_id = d.id
     LEFT JOIN allergens a ON a.id = da.allergen_id
     WHERE md.menu_id = :menu_id
     GROUP BY d.id, d.name, d.description, d.course
     ORDER BY FIELD(d.course, 'Entrée', 'Plat', 'Dessert'), d.name"
);
$request->execute(['menu_id' => $id]);
$dishes = $request->fetchAll();

$request = database()->prepare('SELECT image_url, alt_text FROM menu_images WHERE menu_id = :menu_id ORDER BY id');
$request->execute(['menu_id' => $id]);
$images = $request->fetchAll();
if (!$images) {
    $images = [['image_url' => $menu['image_url'], 'alt_text' => 'Illustration du ' . $menu['title']]];
}

pageHeader($menu['title']);
?>
<main id="contenu" class="container py-5">
  <a href="index.php#menus" class="link-secondary">← Retour aux menus</a>
  <div class="row g-5 mt-1 align-items-start">
    <div class="col-lg-6">
      <?php foreach ($images as $image): ?>
        <img class="img-fluid rounded-4 detail-image mb-3" src="<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($image['alt_text']) ?>">
      <?php endforeach; ?>
    </div>
    <section class="col-lg-6">
      <p class="eyebrow">Menu <?= htmlspecialchars($menu['theme']) ?></p>
      <h1><?= htmlspecialchars($menu['title']) ?></h1>
      <p class="lead"><?= htmlspecialchars($menu['description']) ?></p>
      <p><span class="badge text-bg-light"><?= htmlspecialchars($menu['theme']) ?></span> <span class="badge text-bg-success"><?= htmlspecialchars($menu['diet']) ?></span></p>

      <h2 class="h4 mt-4">Composition</h2>
      <?php if (!$dishes): ?><p>Composition en cours de renseignement. Contactez-nous avant de commander.</p><?php endif; ?>
      <?php foreach ($dishes as $dish): ?>
        <div class="border-bottom py-2">
          <strong><?= htmlspecialchars($dish['course']) ?> : <?= htmlspecialchars($dish['name']) ?></strong>
          <?php if ($dish['description']): ?><p class="mb-1"><?= htmlspecialchars($dish['description']) ?></p><?php endif; ?>
          <p class="small text-secondary mb-0">Allergènes déclarés : <?= $dish['allergen_names'] ? htmlspecialchars($dish['allergen_names']) : 'aucun renseigné — contactez-nous pour confirmation' ?></p>
        </div>
      <?php endforeach; ?>

      <h2 class="h4 mt-4">Conditions du menu</h2>
      <p><?= htmlspecialchars($menu['conditions']) ?></p>
      <p>Il reste <?= (int)$menu['stock'] ?> commande(s) possible(s) pour ce menu.</p>
      <div class="price-box">
        <p class="mb-1">À partir de <strong><?= number_format($menu['price'], 2, ',', ' ') ?> €</strong> par personne</p>
        <p class="mb-0">Minimum <?= (int)$menu['min_people'] ?> personnes</p>
      </div>
      <a class="btn btn-primary btn-lg mt-4" href="order.php?menu_id=<?= (int)$menu['id'] ?>">Commander ce menu</a>
    </section>
  </div>
</main>
<?php pageFooter(); ?>
