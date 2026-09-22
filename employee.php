<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);
$isAdmin = currentUser()['role'] === 'admin';
$spaceTitle = $isAdmin ? 'Espace administrateur — gestion des commandes' : 'Espace employé';

$statuses = [
    'pending' => 'En attente', 'accepted' => 'Acceptée',
    'preparing' => 'En préparation', 'delivering' => 'En livraison',
    'delivered' => 'Livrée', 'waiting_equipment_return' => 'Attente du matériel',
    'completed' => 'Terminée', 'cancelled' => 'Annulée',
];
$nextStatuses = [
    'pending' => ['accepted', 'cancelled'],
    'accepted' => ['preparing', 'cancelled'],
    'preparing' => ['delivering', 'cancelled'],
    'delivering' => ['delivered', 'cancelled'],
    'delivered' => ['waiting_equipment_return', 'completed'],
    'waiting_equipment_return' => ['completed'],
    'completed' => [], 'cancelled' => [],
];
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $orderId = filter_input(INPUT_POST, 'order_id', FILTER_VALIDATE_INT);
    $newStatus = $_POST['status'] ?? '';
    $contactMethod = $_POST['contact_method'] ?? '';
    $reason = trim($_POST['reason'] ?? '');

    $request = database()->prepare('SELECT status, menu_id FROM customer_orders WHERE id = :id');
    $request->execute(['id' => $orderId]);
    $order = $request->fetch();

    if (!$order || !in_array($newStatus, $nextStatuses[$order['status']] ?? [], true)) {
        $error = 'Ce changement de statut n’est pas autorisé.';
    } elseif ($newStatus === 'cancelled' && (!isset($_POST['contacted']) || !in_array($contactMethod, ['phone', 'email'], true) || $reason === '')) {
        $error = 'Pour annuler, contacte le client puis indique le moyen utilisé et le motif.';
    } else {
        try {
            database()->beginTransaction();
            $update = database()->prepare('UPDATE customer_orders SET status = :status, cancel_contact_method = :method, cancel_reason = :reason WHERE id = :id AND status = :old_status');
            $update->execute([
                'status' => $newStatus,
                'method' => $newStatus === 'cancelled' ? $contactMethod : null,
                'reason' => $newStatus === 'cancelled' ? $reason : null,
                'id' => $orderId,
                'old_status' => $order['status'],
            ]);
            if ($update->rowCount() !== 1) {
                throw new RuntimeException('Commande modifiée entre-temps.');
            }
            if ($newStatus === 'cancelled') {
                database()->prepare('UPDATE menus SET stock = stock + 1 WHERE id = :id')->execute(['id' => $order['menu_id']]);
                $contactLog = database()->prepare('INSERT INTO order_contact_logs (order_id, employee_id, contact_method, note) VALUES (:order_id, :employee_id, :method, :note)');
                $contactLog->execute(['order_id' => $orderId, 'employee_id' => currentUser()['id'], 'method' => $contactMethod, 'note' => $reason]);
            }
            $history = database()->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:order_id, :status)');
            $history->execute(['order_id' => $orderId, 'status' => $newStatus]);
            database()->commit();
            header('Location: employee.php?updated=1'); exit;
        } catch (Throwable $exception) {
            if (database()->inTransaction()) database()->rollBack();
            $error = 'Le statut n’a pas pu être modifié.';
        }
    }
}

$filterStatus = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');
$sql = 'SELECT o.*, m.title AS menu_title, u.first_name, u.last_name, u.email, u.phone
        FROM customer_orders o
        JOIN menus m ON m.id = o.menu_id
        JOIN users u ON u.id = o.user_id WHERE 1=1';
$params = [];
if (isset($statuses[$filterStatus])) { $sql .= ' AND o.status = :status'; $params['status'] = $filterStatus; }
if ($search !== '') { $sql .= ' AND (u.email LIKE :search OR u.last_name LIKE :search)'; $params['search'] = '%' . $search . '%'; }
$sql .= ' ORDER BY o.created_at DESC';
$request = database()->prepare($sql);
$request->execute($params);
$orders = $request->fetchAll();

pageHeader($spaceTitle);
?>
<main id="contenu" class="container py-5">
  <p class="eyebrow">Gestion</p>
  <h1 class="h2"><?= $spaceTitle ?></h1>
  <?php if ($isAdmin): ?><p><a href="admin.php">← Retour à l’administration</a></p><?php endif; ?>
  <nav class="mb-4 d-flex flex-wrap gap-2" aria-label="Gestion"><a class="btn btn-outline-dark" href="employee-menus.php">Gérer les menus</a><a class="btn btn-outline-dark" href="employee-dishes.php">Gérer les plats</a><a class="btn btn-outline-dark" href="employee-reviews.php">Valider les avis</a><a class="btn btn-outline-dark" href="employee-hours.php">Gérer les horaires</a></nav>

  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success" role="status">Statut mis à jour.</div><?php endif; ?>
  <?php if (isset($_GET['edited'])): ?><div class="alert alert-success" role="status">Commande modifiée et contact client enregistré.</div><?php endif; ?>
  <?php if (isset($_GET['edit_denied'])): ?><div class="alert alert-warning" role="alert">Cette commande n’est plus modifiable.</div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>

  <h2 class="h4">Commandes</h2>
  <form method="get" class="row g-3 align-items-end mb-4">
    <div class="col-md-4"><label class="form-label" for="status">Statut</label><select class="form-select" name="status" id="status"><option value="">Tous</option><?php foreach ($statuses as $value => $label): ?><option value="<?= $value ?>" <?= $filterStatus === $value ? 'selected' : '' ?>><?= $label ?></option><?php endforeach; ?></select></div>
    <div class="col-md-4"><label class="form-label" for="search">E-mail ou nom du client</label><input class="form-control" name="search" id="search" value="<?= htmlspecialchars($search) ?>"></div>
    <div class="col-md-4"><button class="btn btn-outline-dark" type="submit">Filtrer</button></div>
  </form>

  <?php if (!$orders): ?><p>Aucune commande trouvée.</p><?php endif; ?>
  <?php foreach ($orders as $order): ?>
  <article class="order-panel mb-3">
    <div class="d-flex flex-wrap justify-content-between gap-2"><h3 class="h5">Commande n°<?= (int)$order['id'] ?> — <?= htmlspecialchars($order['menu_title']) ?></h3><strong><?= number_format($order['total_amount'], 2, ',', ' ') ?> €</strong></div>
    <p class="mb-1"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?> · <?= htmlspecialchars($order['email']) ?> · <?= htmlspecialchars($order['phone']) ?></p>
    <p class="mb-1"><?= (int)$order['quantity'] ?> personnes · <?= htmlspecialchars($order['delivery_date']) ?> à <?= htmlspecialchars(substr($order['delivery_time'], 0, 5)) ?></p>
    <p><span class="badge text-bg-secondary"><?= $statuses[$order['status']] ?></span></p>
    <?php if (in_array($order['status'], ['pending', 'accepted'], true)): ?><p><a class="btn btn-sm btn-outline-dark" href="employee-edit-order.php?id=<?= (int)$order['id'] ?>">Modifier après contact client</a></p><?php endif; ?>
    <?php if ($nextStatuses[$order['status']]): ?>
    <form method="post" class="row g-2 align-items-end">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <input type="hidden" name="order_id" value="<?= (int)$order['id'] ?>">
      <div class="col-md-3"><label class="form-label" for="status-<?= (int)$order['id'] ?>">Nouveau statut</label><select class="form-select" name="status" id="status-<?= (int)$order['id'] ?>" required><?php foreach ($nextStatuses[$order['status']] as $value): ?><option value="<?= $value ?>"><?= $statuses[$value] ?></option><?php endforeach; ?></select></div>
      <div class="col-md-3"><label class="form-label" for="method-<?= (int)$order['id'] ?>">Contact pour annulation</label><select class="form-select" name="contact_method" id="method-<?= (int)$order['id'] ?>"><option value="">Sans objet</option><option value="phone">Téléphone</option><option value="email">E-mail</option></select></div>
      <div class="col-md-4"><label class="form-label" for="reason-<?= (int)$order['id'] ?>">Motif d’annulation</label><input class="form-control" name="reason" id="reason-<?= (int)$order['id'] ?>" maxlength="500"></div>
      <div class="col-md-2"><button class="btn btn-primary" type="submit">Mettre à jour</button></div>
      <div class="col-12"><label><input type="checkbox" name="contacted" value="1"> J’ai contacté le client (obligatoire uniquement pour annuler).</label></div>
    </form>
    <p class="form-text mb-0">Avant une annulation, contactez le client puis indiquez le moyen utilisé et le motif.</p>
    <?php endif; ?>
  </article>
  <?php endforeach; ?>
</main>
<?php pageFooter(); ?>
