<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $reviewId = filter_input(INPUT_POST, 'review_id', FILTER_VALIDATE_INT);
    $decision = $_POST['decision'] ?? '';
    if (!$reviewId || !in_array($decision, ['approved', 'rejected'], true)) {
        $error = 'Décision invalide.';
    } else {
        $update = database()->prepare('UPDATE reviews SET status = :status WHERE id = :id AND status = :pending');
        $update->execute(['status' => $decision, 'id' => $reviewId, 'pending' => 'pending']);
        if ($update->rowCount() === 1) { header('Location: employee-reviews.php?updated=1'); exit; }
        $error = 'Cet avis a déjà été traité.';
    }
}

$reviewQuery = database()->prepare(
    'SELECT r.id, r.rating, r.comment, r.created_at, o.id AS order_id, m.title
     FROM reviews r
     JOIN customer_orders o ON o.id = r.order_id
     JOIN menus m ON m.id = o.menu_id
     WHERE r.status = :status ORDER BY r.created_at ASC'
);
$reviewQuery->execute(['status' => 'pending']);
$reviews = $reviewQuery->fetchAll();
pageHeader('Modération des avis');
?>
<main id="contenu" class="container py-5">
  <a href="employee.php">← Retour à la gestion</a>
  <h1 class="h2 mt-3">Avis à valider</h1>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success" role="status">Avis traité.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if (!$reviews): ?><p>Aucun avis en attente.</p><?php endif; ?>
  <?php foreach ($reviews as $review): ?>
    <article class="order-panel mb-3">
      <h2 class="h5">Commande n°<?= (int)$review['order_id'] ?> — <?= htmlspecialchars($review['title']) ?></h2>
      <p class="mb-1">Note : <?= (int)$review['rating'] ?> / 5</p>
      <p><?= nl2br(htmlspecialchars($review['comment'])) ?></p>
      <form method="post" class="d-flex gap-2">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="review_id" value="<?= (int)$review['id'] ?>">
        <button class="btn btn-sm btn-primary" type="submit" name="decision" value="approved">Valider</button>
        <button class="btn btn-sm btn-outline-danger" type="submit" name="decision" value="rejected">Refuser</button>
      </form>
    </article>
  <?php endforeach; ?>
</main>
<?php pageFooter(); ?>
