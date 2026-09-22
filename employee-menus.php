<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);

$themes = ['Noël', 'Pâques', 'Classique', 'Événement'];
$diets = ['Classique', 'Végétarien', 'Vegan'];
$errors = [];
$menu = [
    'id' => '', 'title' => '', 'description' => '', 'theme' => 'Classique',
    'diet' => 'Classique', 'min_people' => 4, 'price' => '', 'stock' => 0,
    'image_url' => '', 'conditions' => '', 'lead_days' => 3,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? 'save';
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($action === 'hide' && $id) {
        $request = database()->prepare('UPDATE menus SET stock = 0 WHERE id = :id');
        $request->execute(['id' => $id]);
        header('Location: employee-menus.php?hidden=1'); exit;
    }

    foreach (array_keys($menu) as $field) {
        if ($field !== 'id') $menu[$field] = trim($_POST[$field] ?? '');
    }
    $menu['id'] = $id ?: '';
    if ($menu['title'] === '' || $menu['description'] === '' || $menu['image_url'] === '') $errors[] = 'Titre, description et image sont obligatoires.';
    if (!in_array($menu['theme'], $themes, true) || !in_array($menu['diet'], $diets, true)) $errors[] = 'Thème ou régime invalide.';
    if ((int)$menu['min_people'] < 1 || (float)$menu['price'] <= 0 || (int)$menu['stock'] < 0) $errors[] = 'Vérifie le nombre de personnes, le prix et le stock.';
    if ((int)$menu['lead_days'] < 0 || (int)$menu['lead_days'] > 365 || $menu['conditions'] === '') $errors[] = 'Indique les conditions et un délai de commande valable.';
    if (!filter_var($menu['image_url'], FILTER_VALIDATE_URL) || !in_array(parse_url($menu['image_url'], PHP_URL_SCHEME), ['http', 'https'], true)) $errors[] = 'L’URL de l’image doit commencer par http ou https.';

    if (!$errors) {
        $data = [
            'title' => $menu['title'], 'description' => $menu['description'],
            'theme' => $menu['theme'], 'diet' => $menu['diet'],
            'min_people' => (int)$menu['min_people'], 'price' => (float)$menu['price'],
            'stock' => (int)$menu['stock'], 'image_url' => $menu['image_url'],
            'conditions' => $menu['conditions'], 'lead_days' => (int)$menu['lead_days'],
        ];
        if ($id) {
            $data['id'] = $id;
            $sql = 'UPDATE menus SET title=:title, description=:description, theme=:theme, diet=:diet, min_people=:min_people, price=:price, stock=:stock, image_url=:image_url, conditions=:conditions, lead_days=:lead_days WHERE id=:id';
        } else {
            $sql = 'INSERT INTO menus (title, description, theme, diet, min_people, price, stock, image_url, conditions, lead_days) VALUES (:title, :description, :theme, :diet, :min_people, :price, :stock, :image_url, :conditions, :lead_days)';
        }
        database()->beginTransaction();
        try {
            $request = database()->prepare($sql);
            $request->execute($data);
            $savedId = $id ?: (int)database()->lastInsertId();
            $firstImage = database()->prepare('SELECT id FROM menu_images WHERE menu_id = :id ORDER BY id LIMIT 1');
            $firstImage->execute(['id' => $savedId]);
            $imageId = $firstImage->fetchColumn();
            $imageData = ['url' => $menu['image_url'], 'alt' => 'Illustration du ' . $menu['title']];
            if ($imageId) {
                $imageData['id'] = $imageId;
                database()->prepare('UPDATE menu_images SET image_url = :url, alt_text = :alt WHERE id = :id')->execute($imageData);
            } else {
                $imageData['menu_id'] = $savedId;
                database()->prepare('INSERT INTO menu_images (menu_id, image_url, alt_text) VALUES (:menu_id, :url, :alt)')->execute($imageData);
            }
            database()->commit();
        } catch (Throwable $exception) {
            database()->rollBack();
            $errors[] = 'Le menu n’a pas pu être enregistré.';
        }
        if (!$errors) { header('Location: employee-menus.php?saved=1'); exit; }
    }
} elseif (isset($_GET['id'])) {
    $request = database()->prepare('SELECT * FROM menus WHERE id = :id');
    $request->execute(['id' => filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)]);
    $found = $request->fetch();
    if ($found) $menu = $found;
}

$menus = database()->query('SELECT id, title, price, min_people, stock FROM menus ORDER BY id DESC')->fetchAll();
pageHeader('Gestion des menus');
?>
<main id="contenu" class="container py-5">
  <a href="employee.php">← Retour à l’espace employé</a>
  <h1 class="h2 mt-3">Gestion des menus</h1>
  <p>Un menu retiré du catalogue garde son historique dans les anciennes commandes.</p>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success" role="status">Menu enregistré.</div><?php endif; ?>
  <?php if (isset($_GET['hidden'])): ?><div class="alert alert-success" role="status">Menu retiré du catalogue.</div><?php endif; ?>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>

  <div class="row g-4">
    <section class="col-lg-7">
      <div class="order-panel">
        <h2 class="h4"><?= $menu['id'] ? 'Modifier le menu' : 'Créer un menu' ?></h2>
        <form method="post">
          <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
          <input type="hidden" name="id" value="<?= htmlspecialchars((string)$menu['id']) ?>">
          <div class="mb-3"><label class="form-label" for="title">Titre</label><input required class="form-control" id="title" name="title" value="<?= htmlspecialchars($menu['title']) ?>"></div>
          <div class="mb-3"><label class="form-label" for="description">Description</label><textarea required class="form-control" id="description" name="description" rows="3"><?= htmlspecialchars($menu['description']) ?></textarea></div>
          <div class="row g-3">
            <div class="col-md-6"><label class="form-label" for="theme">Thème</label><select class="form-select" id="theme" name="theme"><?php foreach ($themes as $theme): ?><option <?= $menu['theme'] === $theme ? 'selected' : '' ?>><?= $theme ?></option><?php endforeach; ?></select></div>
            <div class="col-md-6"><label class="form-label" for="diet">Régime</label><select class="form-select" id="diet" name="diet"><?php foreach ($diets as $diet): ?><option <?= $menu['diet'] === $diet ? 'selected' : '' ?>><?= $diet ?></option><?php endforeach; ?></select></div>
            <div class="col-md-4"><label class="form-label" for="min_people">Personnes minimum</label><input required type="number" min="1" class="form-control" id="min_people" name="min_people" value="<?= htmlspecialchars((string)$menu['min_people']) ?>"></div>
            <div class="col-md-4"><label class="form-label" for="price">Prix par personne (€)</label><input required type="number" min="0.01" step="0.01" class="form-control" id="price" name="price" value="<?= htmlspecialchars((string)$menu['price']) ?>"></div>
            <div class="col-md-4"><label class="form-label" for="stock">Stock</label><input required type="number" min="0" class="form-control" id="stock" name="stock" value="<?= htmlspecialchars((string)$menu['stock']) ?>"></div>
          </div>
          <div class="my-3"><label class="form-label" for="image_url">URL de l’image</label><input required type="url" class="form-control" id="image_url" name="image_url" value="<?= htmlspecialchars($menu['image_url']) ?>"></div>
          <div class="mb-3"><label class="form-label" for="conditions">Conditions du menu</label><textarea required maxlength="500" class="form-control" id="conditions" name="conditions" rows="2"><?= htmlspecialchars($menu['conditions']) ?></textarea></div>
          <div class="mb-3"><label class="form-label" for="lead_days">Délai de commande (jours)</label><input required type="number" min="0" max="365" class="form-control" id="lead_days" name="lead_days" value="<?= (int)$menu['lead_days'] ?>"></div>
          <button class="btn btn-primary" type="submit">Enregistrer le menu</button>
          <?php if ($menu['id']): ?><a class="btn btn-outline-secondary" href="employee-menus.php">Créer un autre menu</a> <a class="btn btn-outline-secondary" href="employee-images.php?menu_id=<?= (int)$menu['id'] ?>">Gérer les images</a><?php endif; ?>
        </form>
      </div>
    </section>
    <section class="col-lg-5"><h2 class="h4">Menus existants</h2>
      <?php foreach ($menus as $item): ?><div class="border rounded p-3 mb-2 bg-white"><div class="d-flex justify-content-between"><strong><?= htmlspecialchars($item['title']) ?></strong><span><?= number_format($item['price'], 2, ',', ' ') ?> €</span></div><p class="small mb-2">Min. <?= (int)$item['min_people'] ?> personnes · Stock <?= (int)$item['stock'] ?></p><a class="btn btn-sm btn-outline-dark" href="employee-menus.php?id=<?= (int)$item['id'] ?>">Modifier</a> <?php if ($item['stock'] > 0): ?><form method="post" class="d-inline"><input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="action" value="hide"><button class="btn btn-sm btn-outline-danger" type="submit">Retirer du catalogue</button></form><?php endif; ?></div><?php endforeach; ?>
    </section>
  </div>
</main>
<?php pageFooter(); ?>
