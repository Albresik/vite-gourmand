<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);

$courses = ['Entrée', 'Plat', 'Dessert'];
$errors = [];
$dish = ['id' => '', 'name' => '', 'description' => '', 'course' => 'Entrée'];
$selectedMenus = [];
$selectedAllergens = [];

$menus = database()->query('SELECT id, title FROM menus ORDER BY title')->fetchAll();
$allergens = database()->query('SELECT id, name FROM allergens ORDER BY name')->fetchAll();
$validMenuIds = array_map('intval', array_column($menus, 'id'));
$validAllergenIds = array_map('intval', array_column($allergens, 'id'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $action = $_POST['action'] ?? 'save';

    if ($action === 'delete') {
        if (!$id) {
            $errors[] = 'Plat introuvable.';
        } else {
            database()->prepare('DELETE FROM dishes WHERE id = :id')->execute(['id' => $id]);
            header('Location: employee-dishes.php?deleted=1'); exit;
        }
    } else {
        $dish = [
            'id' => $id ?: '',
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'course' => $_POST['course'] ?? '',
        ];
        $selectedMenus = array_map('intval', (array)($_POST['menus'] ?? []));
        $selectedAllergens = array_map('intval', (array)($_POST['allergens'] ?? []));

        if ($dish['name'] === '' || !in_array($dish['course'], $courses, true)) {
            $errors[] = 'Indique le nom et le type de plat.';
        }
        if (array_diff($selectedMenus, $validMenuIds) || array_diff($selectedAllergens, $validAllergenIds)) {
            $errors[] = 'Un menu ou un allergène sélectionné est invalide.';
        }

        if (!$errors) {
            try {
                database()->beginTransaction();
                $values = ['name' => $dish['name'], 'description' => $dish['description'], 'course' => $dish['course']];
                if ($id) {
                    $values['id'] = $id;
                    database()->prepare('UPDATE dishes SET name = :name, description = :description, course = :course WHERE id = :id')->execute($values);
                    database()->prepare('DELETE FROM menu_dishes WHERE dish_id = :id')->execute(['id' => $id]);
                    database()->prepare('DELETE FROM dish_allergens WHERE dish_id = :id')->execute(['id' => $id]);
                } else {
                    database()->prepare('INSERT INTO dishes (name, description, course) VALUES (:name, :description, :course)')->execute($values);
                    $id = (int)database()->lastInsertId();
                }
                $addMenu = database()->prepare('INSERT INTO menu_dishes (menu_id, dish_id) VALUES (:menu_id, :dish_id)');
                foreach (array_unique($selectedMenus) as $menuId) $addMenu->execute(['menu_id' => $menuId, 'dish_id' => $id]);
                $addAllergen = database()->prepare('INSERT INTO dish_allergens (dish_id, allergen_id) VALUES (:dish_id, :allergen_id)');
                foreach (array_unique($selectedAllergens) as $allergenId) $addAllergen->execute(['dish_id' => $id, 'allergen_id' => $allergenId]);
                database()->commit();
                header('Location: employee-dishes.php?saved=1'); exit;
            } catch (Throwable $exception) {
                if (database()->inTransaction()) database()->rollBack();
                $errors[] = 'Le plat n’a pas pu être enregistré.';
            }
        }
    }
} elseif (isset($_GET['id'])) {
    $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $request = database()->prepare('SELECT * FROM dishes WHERE id = :id');
    $request->execute(['id' => $id]);
    $dish = $request->fetch() ?: $dish;
    if ($dish['id']) {
        $request = database()->prepare('SELECT menu_id FROM menu_dishes WHERE dish_id = :id');
        $request->execute(['id' => $id]);
        $selectedMenus = array_map('intval', $request->fetchAll(PDO::FETCH_COLUMN));
        $request = database()->prepare('SELECT allergen_id FROM dish_allergens WHERE dish_id = :id');
        $request->execute(['id' => $id]);
        $selectedAllergens = array_map('intval', $request->fetchAll(PDO::FETCH_COLUMN));
    }
}

$dishes = database()->query('SELECT id, name, course FROM dishes ORDER BY course, name')->fetchAll();
pageHeader('Gestion des plats');
?>
<main id="contenu" class="container py-5">
  <a href="employee.php">← Retour à l’espace employé</a>
  <h1 class="h2 mt-3">Gestion des plats</h1>
  <p>Un plat peut appartenir à plusieurs menus. Les allergènes sont renseignés par plat.</p>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success" role="status">Plat enregistré.</div><?php endif; ?>
  <?php if (isset($_GET['deleted'])): ?><div class="alert alert-success" role="status">Plat supprimé.</div><?php endif; ?>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>

  <div class="row g-4">
    <section class="col-lg-7"><div class="order-panel">
      <h2 class="h4"><?= $dish['id'] ? 'Modifier le plat' : 'Créer un plat' ?></h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="id" value="<?= htmlspecialchars((string)$dish['id']) ?>">
        <div class="mb-3"><label class="form-label" for="name">Nom</label><input required maxlength="120" class="form-control" id="name" name="name" value="<?= htmlspecialchars($dish['name']) ?>"></div>
        <div class="mb-3"><label class="form-label" for="description">Description</label><input maxlength="255" class="form-control" id="description" name="description" value="<?= htmlspecialchars($dish['description']) ?>"></div>
        <div class="mb-3"><label class="form-label" for="course">Type</label><select class="form-select" id="course" name="course"><?php foreach ($courses as $course): ?><option <?= $dish['course'] === $course ? 'selected' : '' ?>><?= $course ?></option><?php endforeach; ?></select></div>
        <fieldset class="mb-3"><legend class="fs-5">Menus contenant ce plat</legend>
          <?php foreach ($menus as $menu): ?><label class="d-block"><input type="checkbox" name="menus[]" value="<?= (int)$menu['id'] ?>" <?= in_array((int)$menu['id'], $selectedMenus, true) ? 'checked' : '' ?>> <?= htmlspecialchars($menu['title']) ?></label><?php endforeach; ?>
        </fieldset>
        <fieldset class="mb-3"><legend class="fs-5">Allergènes</legend>
          <?php foreach ($allergens as $allergen): ?><label class="d-block"><input type="checkbox" name="allergens[]" value="<?= (int)$allergen['id'] ?>" <?= in_array((int)$allergen['id'], $selectedAllergens, true) ? 'checked' : '' ?>> <?= htmlspecialchars($allergen['name']) ?></label><?php endforeach; ?>
        </fieldset>
        <button class="btn btn-primary" type="submit">Enregistrer</button>
        <?php if ($dish['id']): ?><a class="btn btn-outline-secondary" href="employee-dishes.php">Créer un autre plat</a><?php endif; ?>
      </form>
    </div></section>
    <section class="col-lg-5"><h2 class="h4">Plats existants</h2>
      <?php foreach ($dishes as $item): ?><div class="border rounded p-3 mb-2 bg-white"><strong><?= htmlspecialchars($item['name']) ?></strong> <span class="text-secondary">(<?= htmlspecialchars($item['course']) ?>)</span><div class="mt-2"><a class="btn btn-sm btn-outline-dark" href="employee-dishes.php?id=<?= (int)$item['id'] ?>">Modifier</a> <form method="post" class="d-inline" onsubmit="return confirm('Supprimer ce plat de tous les menus ?')"><input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="id" value="<?= (int)$item['id'] ?>"><input type="hidden" name="action" value="delete"><button class="btn btn-sm btn-outline-danger" type="submit">Supprimer</button></form></div></div><?php endforeach; ?>
    </section>
  </div>
</main>
<?php pageFooter(); ?>
