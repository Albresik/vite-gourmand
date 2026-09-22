<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['user']);

$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
$request = database()->prepare(
    'SELECT o.id, o.status, m.title, r.id AS review_id
     FROM customer_orders o
     JOIN menus m ON m.id = o.menu_id
     LEFT JOIN reviews r ON r.order_id = o.id
     WHERE o.id = :id AND o.user_id = :user_id'
);
$request->execute(['id' => $orderId, 'user_id' => currentUser()['id']]);
$order = $request->fetch();
if (!$order) { http_response_code(404); exit('Commande introuvable.'); }
if ($order['status'] !== 'completed' || $order['review_id']) {
    header('Location: account.php?review_denied=1'); exit;
}

$rating = '';
$comment = '';
$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $rating = filter_input(INPUT_POST, 'rating', FILTER_VALIDATE_INT);
    $comment = trim($_POST['comment'] ?? '');
    if ($rating === false || $rating < 1 || $rating > 5) $errors[] = 'Choisis une note entre 1 et 5.';
    if ($comment === '' || strlen($comment) > 1000) $errors[] = 'Écris un commentaire de 1 à 1000 caractères.';

    if (!$errors) {
        try {
            $insert = database()->prepare('INSERT INTO reviews (order_id, rating, comment) VALUES (:order_id, :rating, :comment)');
            $insert->execute(['order_id' => $orderId, 'rating' => $rating, 'comment' => $comment]);
            header('Location: account.php?review_sent=1'); exit;
        } catch (PDOException $exception) {
            $errors[] = 'Cet avis n’a pas pu être enregistré. Il existe peut-être déjà.';
        }
    }
}

pageHeader('Donner mon avis');
?>
<main id="contenu" class="container py-5">
  <a href="account.php">← Retour à mon espace</a>
  <div class="auth-card mx-auto mt-3">
    <h1 class="h2">Donner mon avis</h1>
    <p>Commande n°<?= (int)$orderId ?> — <?= htmlspecialchars($order['title']) ?></p>
    <p>Ton avis sera visible après validation par notre équipe.</p>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="order_id" value="<?= (int)$orderId ?>">
      <div class="mb-3"><label class="form-label" for="rating">Note</label><select required class="form-select" id="rating" name="rating"><option value="">Choisir une note</option><?php for ($note = 1; $note <= 5; $note++): ?><option value="<?= $note ?>" <?= (int)$rating === $note ? 'selected' : '' ?>><?= $note ?> / 5</option><?php endfor; ?></select></div>
      <div class="mb-3"><label class="form-label" for="comment">Commentaire</label><textarea required maxlength="1000" rows="5" class="form-control" id="comment" name="comment"><?= htmlspecialchars($comment) ?></textarea></div>
      <button class="btn btn-primary" type="submit">Envoyer mon avis</button>
    </form>
  </div>
</main>
<?php pageFooter(); ?>
