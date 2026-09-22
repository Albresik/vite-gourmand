<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);

$menuId = filter_input(INPUT_GET, 'menu_id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'menu_id', FILTER_VALIDATE_INT);
$request = database()->prepare('SELECT id, title FROM menus WHERE id = :id');
$request->execute(['id' => $menuId]);
$menu = $request->fetch();
if (!$menu) { header('Location: employee-menus.php'); exit; }

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    if (($_POST['action'] ?? '') === 'delete') {
        $imageId = filter_input(INPUT_POST, 'image_id', FILTER_VALIDATE_INT);
        $first = database()->prepare('SELECT id FROM menu_images WHERE menu_id = :menu_id ORDER BY id LIMIT 1');
        $first->execute(['menu_id' => $menuId]);
        if ($imageId && (int)$first->fetchColumn() !== $imageId) {
            database()->prepare('DELETE FROM menu_images WHERE id = :id AND menu_id = :menu_id')->execute(['id' => $imageId, 'menu_id' => $menuId]);
            header('Location: employee-images.php?menu_id=' . $menuId . '&deleted=1'); exit;
        }
        $error = 'L’image principale se modifie depuis la fiche du menu.';
    } else {
        $url = trim($_POST['image_url'] ?? '');
        $alt = trim($_POST['alt_text'] ?? '');
        if (!filter_var($url, FILTER_VALIDATE_URL) || !in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true) || $alt === '') {
            $error = 'Indique une URL http(s) et une description de l’image.';
        } else {
            $insert = database()->prepare('INSERT INTO menu_images (menu_id, image_url, alt_text) VALUES (:menu_id, :url, :alt)');
            $insert->execute(['menu_id' => $menuId, 'url' => $url, 'alt' => $alt]);
            header('Location: employee-images.php?menu_id=' . $menuId . '&saved=1'); exit;
        }
    }
}

$request = database()->prepare('SELECT id, image_url, alt_text FROM menu_images WHERE menu_id = :menu_id ORDER BY id');
$request->execute(['menu_id' => $menuId]);
$images = $request->fetchAll();
pageHeader('Images du menu');
?>
<main id="contenu" class="container py-5">
  <a href="employee-menus.php?id=<?= (int)$menuId ?>">← Retour au menu</a>
  <h1 class="h2 mt-3">Images : <?= htmlspecialchars($menu['title']) ?></h1>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success" role="status">Image ajoutée.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" role="status">Image supprimée.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <section class="order-panel mb-4"><h2 class="h4">Ajouter une image</h2>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="menu_id" value="<?= (int)$menuId ?>">
      <div class="mb-3"><label class="form-label" for="image_url">URL de l’image</label><input required type="url" class="form-control" id="image_url" name="image_url"></div>
      <div class="mb-3"><label class="form-label" for="alt_text">Description de l’image</label><input required maxlength="160" class="form-control" id="alt_text" name="alt_text"></div>
      <button class="btn btn-primary" type="submit">Ajouter</button>
    </form>
  </section>
  <section><h2 class="h4">Galerie</h2><div class="row g-3">
    <?php foreach ($images as $index => $image): ?><div class="col-md-4"><div class="border rounded p-3 h-100 bg-white"><img class="img-fluid rounded mb-2" src="<?= htmlspecialchars($image['image_url']) ?>" alt="<?= htmlspecialchars($image['alt_text']) ?>"><p><?= htmlspecialchars($image['alt_text']) ?></p><?php if ($index === 0): ?><span class="badge text-bg-secondary">Image principale</span><?php else: ?><form method="post"><input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="menu_id" value="<?= (int)$menuId ?>"><input type="hidden" name="image_id" value="<?= (int)$image['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button></form><?php endif; ?></div></div><?php endforeach; ?>
  </div></section>
</main>
<?php pageFooter(); ?>
