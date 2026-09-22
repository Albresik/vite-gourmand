<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['user']);

$userId = (int)currentUser()['id'];
$statuses = [
    'pending' => 'En attente', 'accepted' => 'Acceptée',
    'preparing' => 'En préparation', 'delivering' => 'En livraison',
    'delivered' => 'Livrée', 'waiting_equipment_return' => 'En attente du matériel',
    'completed' => 'Terminée', 'cancelled' => 'Annulée',
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    if (!$orderId) {
        $error = 'Commande introuvable.';
    } else {
        try {
            database()->beginTransaction();
            $request = database()->prepare('SELECT menu_id, status FROM customer_orders WHERE id = :id AND user_id = :user_id FOR UPDATE');
            $request->execute(['id' => $orderId, 'user_id' => $userId]);
            $order = $request->fetch();

            if (!$order || $order['status'] !== 'pending') {
                $error = 'Cette commande ne peut plus être annulée depuis votre espace.';
                database()->rollBack();
            } else {
                database()->prepare('UPDATE customer_orders SET status = "cancelled" WHERE id = :id')->execute(['id' => $orderId]);
                database()->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:id, "cancelled")')->execute(['id' => $orderId]);
                database()->prepare('UPDATE menus SET stock = stock + 1 WHERE id = :id')->execute(['id' => $order['menu_id']]);
                database()->commit();
                header('Location: account.php?cancelled=1'); exit;
            }
        } catch (Throwable $exception) {
            if (database()->inTransaction()) database()->rollBack();
            $error = 'L’annulation n’a pas pu être enregistrée.';
        }
    }
}

$request = database()->prepare('SELECT first_name, last_name, email, phone, address FROM users WHERE id = :id');
$request->execute(['id' => $userId]);
$profile = $request->fetch();

$request = database()->prepare('SELECT o.*, m.title, r.id AS review_id, r.status AS review_status FROM customer_orders o JOIN menus m ON m.id = o.menu_id LEFT JOIN reviews r ON r.order_id = o.id WHERE o.user_id = :user_id ORDER BY o.created_at DESC');
$request->execute(['user_id' => $userId]);
$orders = $request->fetchAll();

$historyRequest = database()->prepare('SELECT status, created_at FROM order_status_history WHERE order_id = :id ORDER BY id');
foreach ($orders as &$order) {
    $historyRequest->execute(['id' => $order['id']]);
    $order['history'] = $historyRequest->fetchAll();
}
unset($order);

pageHeader('Mon espace');
?>
<main id="contenu" class="container py-5">
  <p class="eyebrow">Espace utilisateur</p>
  <h1 class="h2">Bonjour <?= htmlspecialchars($profile['first_name']) ?> !</h1>
  <?php if (isset($_GET['cancelled'])): ?><div class="alert alert-success mt-3" role="status">Commande annulée.</div><?php endif; ?>
  <?php if (isset($_GET['edited'])): ?><div class="alert alert-success mt-3" role="status">Commande modifiée.</div><?php endif; ?>
  <?php if (isset($_GET['edit_denied'])): ?><div class="alert alert-warning mt-3" role="alert">Cette commande n’est plus modifiable.</div><?php endif; ?>
  <?php if (isset($_GET['profile_saved'])): ?><div class="alert alert-success mt-3" role="status">Profil mis à jour.</div><?php endif; ?>
  <?php if (isset($_GET['review_sent'])): ?><div class="alert alert-success mt-3" role="status">Avis envoyé. Il sera visible après validation.</div><?php endif; ?>
  <?php if (isset($_GET['review_denied'])): ?><div class="alert alert-warning mt-3" role="alert">L’avis n’est pas disponible pour cette commande.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger mt-3" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <div class="row g-4 mt-2">
    <section class="col-lg-8">
      <h2 class="h4">Mes commandes</h2>
      <?php if (!$orders): ?><p>Aucune commande pour le moment. <a href="index.php#menus">Découvrir les menus</a></p><?php endif; ?>
      <?php foreach ($orders as $order): ?>
        <article class="order-panel mb-3">
          <div class="d-flex flex-wrap justify-content-between gap-2">
            <h3 class="h5">Commande n°<?= (int)$order['id'] ?> — <?= htmlspecialchars($order['title']) ?></h3>
            <strong><?= number_format($order['total_amount'], 2, ',', ' ') ?> €</strong>
          </div>
          <p class="mb-1"><?= (int)$order['quantity'] ?> personnes · <?= htmlspecialchars($order['delivery_date']) ?> à <?= htmlspecialchars(substr($order['delivery_time'], 0, 5)) ?></p>
          <p class="mb-2">Lieu : <?= htmlspecialchars($order['delivery_address']) ?></p>
          <p><span class="badge text-bg-secondary"><?= htmlspecialchars($statuses[$order['status']] ?? $order['status']) ?></span></p>

          <details>
            <summary>Voir le suivi de la commande</summary>
            <ol class="mt-2">
              <?php foreach ($order['history'] as $step): ?>
                <li><?= htmlspecialchars($statuses[$step['status']] ?? $step['status']) ?> — <?= htmlspecialchars(date('d/m/Y à H:i', strtotime($step['created_at']))) ?></li>
              <?php endforeach; ?>
            </ol>
          </details>

          <?php if ($order['status'] === 'pending'): ?>
            <a class="btn btn-sm btn-outline-dark mt-3" href="edit-order.php?id=<?= (int)$order['id'] ?>">Modifier ma commande</a>
            <form method="post" class="mt-3" onsubmit="return confirm('Annuler cette commande ?')">
              <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
              <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
              <button class="btn btn-sm btn-outline-danger" type="submit">Annuler ma commande</button>
            </form>
          <?php endif; ?>
          <?php if ($order['status'] === 'completed'): ?>
            <?php if (!$order['review_id']): ?><p class="mt-3 mb-0"><a class="btn btn-sm btn-outline-dark" href="review.php?order_id=<?= (int)$order['id'] ?>">Donner mon avis</a></p>
            <?php else: ?><p class="small text-secondary mt-3 mb-0">Avis : <?= htmlspecialchars(['pending' => 'en attente de validation', 'approved' => 'publié', 'rejected' => 'non retenu'][$order['review_status']] ?? $order['review_status']) ?></p><?php endif; ?>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </section>

    <aside class="col-lg-4"><div class="order-panel">
      <h2 class="h4">Mon profil</h2>
      <dl class="mb-0">
        <dt>Nom</dt><dd><?= htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']) ?></dd>
        <dt>E-mail</dt><dd><?= htmlspecialchars($profile['email']) ?></dd>
        <dt>Téléphone</dt><dd><?= htmlspecialchars($profile['phone']) ?></dd>
        <dt>Adresse</dt><dd><?= htmlspecialchars($profile['address']) ?></dd>
      </dl>
      <a class="btn btn-sm btn-outline-dark mt-2" href="profile.php">Modifier mes informations</a>
    </div></aside>
  </div>
</main>
<?php pageFooter(); ?>
