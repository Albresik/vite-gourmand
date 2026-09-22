<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);

$days = [1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
$request = database()->query('SELECT * FROM opening_hours ORDER BY day_of_week');
$hours = [];
foreach ($request->fetchAll() as $row) $hours[(int)$row['day_of_week']] = $row;
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $updated = [];
    foreach ($days as $day => $label) {
        $closed = isset($_POST['closed'][$day]);
        $open = trim($_POST['open'][$day] ?? '');
        $close = trim($_POST['close'][$day] ?? '');
        if (!$closed && (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $open)
            || !preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $close) || $close <= $open)) {
            $errors[] = 'Vérifie les horaires de ' . $label . '.';
        }
        $updated[$day] = [
            'day' => $day, 'closed' => $closed ? 1 : 0,
            'open' => $closed ? null : $open, 'close' => $closed ? null : $close,
        ];
        $hours[$day] = [
            'day_of_week' => $day, 'is_closed' => $closed ? 1 : 0,
            'opening_time' => $open, 'closing_time' => $close,
        ];
    }

    if (!$errors) {
        try {
            database()->beginTransaction();
            $save = database()->prepare('UPDATE opening_hours SET opening_time = :open, closing_time = :close, is_closed = :closed WHERE day_of_week = :day');
            foreach ($updated as $row) $save->execute($row);
            database()->commit();
            header('Location: employee-hours.php?saved=1'); exit;
        } catch (Throwable $exception) {
            if (database()->inTransaction()) database()->rollBack();
            $errors[] = 'Les horaires n’ont pas pu être enregistrés.';
        }
    }
}

pageHeader('Gestion des horaires');
?>
<main id="contenu" class="container py-5">
  <a href="employee.php">← Retour à la gestion</a>
  <h1 class="h2 mt-3">Horaires d’ouverture</h1>
  <p>Ces horaires s’affichent en bas de chaque page du site.</p>
  <?php if (isset($_GET['saved'])): ?><div class="alert alert-success" role="status">Horaires enregistrés.</div><?php endif; ?>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
  <form method="post" class="order-panel">
    <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
    <?php foreach ($days as $day => $label): $row = $hours[$day]; ?>
      <fieldset class="border-bottom py-3">
        <legend class="fs-5"><?= $label ?></legend>
        <div class="row g-3 align-items-end">
          <div class="col-md-3"><label class="form-label" for="open-<?= $day ?>">Ouverture</label><input type="time" class="form-control" id="open-<?= $day ?>" name="open[<?= $day ?>]" value="<?= htmlspecialchars(substr((string)$row['opening_time'], 0, 5)) ?>"></div>
          <div class="col-md-3"><label class="form-label" for="close-<?= $day ?>">Fermeture</label><input type="time" class="form-control" id="close-<?= $day ?>" name="close[<?= $day ?>]" value="<?= htmlspecialchars(substr((string)$row['closing_time'], 0, 5)) ?>"></div>
          <div class="col-md-3"><label><input type="checkbox" name="closed[<?= $day ?>]" value="1" <?= $row['is_closed'] ? 'checked' : '' ?>> Fermé ce jour</label></div>
        </div>
      </fieldset>
    <?php endforeach; ?>
    <button class="btn btn-primary mt-4" type="submit">Enregistrer les horaires</button>
  </form>
</main>
<?php pageFooter(); ?>
