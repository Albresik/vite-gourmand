<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['employee', 'admin']);

$orderId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
$request = database()->prepare(
    'SELECT o.*, m.title, m.min_people, m.lead_days, u.first_name, u.last_name, u.email, u.phone
     FROM customer_orders o
     JOIN menus m ON m.id = o.menu_id
     JOIN users u ON u.id = o.user_id
     WHERE o.id = :id'
);
$request->execute(['id' => $orderId]);
$order = $request->fetch();
if (!$order) { http_response_code(404); exit('Commande introuvable.'); }
if (!in_array($order['status'], ['pending', 'accepted'], true)) {
    header('Location: employee.php?edit_denied=1'); exit;
}

$unitPrice = round((float)$order['menu_total'] / (int)$order['quantity'], 2);
$minDate = (new DateTimeImmutable('today'))->modify('+' . (int)$order['lead_days'] . ' days')->format('Y-m-d');
$data = [
    'delivery_address' => $order['delivery_address'],
    'delivery_date' => $order['delivery_date'],
    'delivery_time' => substr($order['delivery_time'], 0, 5),
    'distance_km' => $order['distance_km'],
    'quantity' => $order['quantity'],
];
$errors = [];
$contactMethod = '';
$note = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($data as $field => $default) $data[$field] = trim((string)($_POST[$field] ?? $default));
    $contactMethod = $_POST['contact_method'] ?? '';
    $note = trim($_POST['note'] ?? '');
    $contacted = isset($_POST['contacted']);
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
    $distance = is_numeric($data['distance_km']) ? (float)$data['distance_km'] : -1;
    $inBordeaux = stripos($data['delivery_address'], 'bordeaux') !== false;
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $data['delivery_date']);

    if (!$contacted || !in_array($contactMethod, ['phone', 'email'], true) || $note === '') {
        $errors[] = 'Confirme le contact client, son moyen et le motif de la modification.';
    }
    if (strlen($note) > 500) $errors[] = 'Le motif ne doit pas dépasser 500 caractères.';
    if ($data['delivery_address'] === '' || $data['delivery_date'] === '' || $data['delivery_time'] === '') {
        $errors[] = 'Complète les informations de livraison.';
    }
    if ($quantity === false || $quantity < $order['min_people'] || $quantity > 500) {
        $errors[] = 'Le nombre de personnes doit être compris entre ' . $order['min_people'] . ' et 500.';
    }
    if (!$date || $date->format('Y-m-d') !== $data['delivery_date'] || $data['delivery_date'] < $minDate) {
        $errors[] = 'La date doit respecter le délai de commande du menu.';
    }
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $data['delivery_time'])) {
        $errors[] = 'L’heure de livraison est invalide.';
    }
    if ($distance < 0 || $distance > 1000 || (!$inBordeaux && $distance == 0)) {
        $errors[] = 'Indique une distance positive pour une livraison hors Bordeaux.';
    }

    if (!$errors) {
        $menuTotal = round($unitPrice * $quantity, 2);
        $discount = $quantity >= $order['min_people'] + 5 ? round($menuTotal * 0.10, 2) : 0;
        $delivery = $inBordeaux ? 5 : round(5 + 0.59 * $distance, 2);
        $total = $menuTotal - $discount + $delivery;

        try {
            database()->beginTransaction();
            $lock = database()->prepare('SELECT status FROM customer_orders WHERE id = :id FOR UPDATE');
            $lock->execute(['id' => $orderId]);
            if (!in_array($lock->fetchColumn(), ['pending', 'accepted'], true)) {
                throw new RuntimeException('La commande a changé de statut.');
            }

            $update = database()->prepare(
                'UPDATE customer_orders SET quantity = :quantity, delivery_address = :address,
                 delivery_date = :date, delivery_time = :time, distance_km = :distance,
                 menu_total = :menu_total, discount_amount = :discount,
                 delivery_fee = :delivery, total_amount = :total WHERE id = :id'
            );
            $update->execute([
                'quantity' => $quantity, 'address' => $data['delivery_address'],
                'date' => $data['delivery_date'], 'time' => $data['delivery_time'],
                'distance' => $distance, 'menu_total' => $menuTotal,
                'discount' => $discount, 'delivery' => $delivery,
                'total' => $total, 'id' => $orderId,
            ]);
            $log = database()->prepare('INSERT INTO order_contact_logs (order_id, employee_id, contact_method, note) VALUES (:order_id, :employee_id, :method, :note)');
            $log->execute([
                'order_id' => $orderId, 'employee_id' => currentUser()['id'],
                'method' => $contactMethod, 'note' => $note,
            ]);
            database()->commit();
            header('Location: employee.php?edited=1'); exit;
        } catch (Throwable $exception) {
            if (database()->inTransaction()) database()->rollBack();
            $errors[] = 'La commande n’a pas pu être modifiée. Vérifie son statut.';
        }
    }
}

pageHeader('Modifier une commande');
?>
<main id="contenu" class="container py-5">
  <a href="employee.php">← Retour aux commandes</a>
  <h1 class="h2 mt-3">Modifier la commande n°<?= (int)$orderId ?></h1>
  <p><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?> · <?= htmlspecialchars($order['email']) ?> · <?= htmlspecialchars($order['phone']) ?></p>
  <p>Menu : <strong><?= htmlspecialchars($order['title']) ?></strong> (non modifiable).</p>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>

  <div class="row g-4">
    <section class="col-lg-7"><div class="order-panel">
      <form method="post" id="order-form" data-price="<?= htmlspecialchars((string)$unitPrice) ?>" data-min="<?= (int)$order['min_people'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="id" value="<?= (int)$orderId ?>">
        <div class="mb-3"><label class="form-label" for="delivery_address">Adresse de prestation</label><input required class="form-control" id="delivery_address" name="delivery_address" value="<?= htmlspecialchars($data['delivery_address']) ?>"></div>
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label" for="delivery_date">Date</label><input required type="date" min="<?= $minDate ?>" class="form-control" id="delivery_date" name="delivery_date" value="<?= htmlspecialchars($data['delivery_date']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="delivery_time">Heure</label><input required type="time" class="form-control" id="delivery_time" name="delivery_time" value="<?= htmlspecialchars($data['delivery_time']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="distance_km">Distance hors Bordeaux (km)</label><input required type="number" min="0" step="0.1" class="form-control" id="distance_km" name="distance_km" value="<?= htmlspecialchars((string)$data['distance_km']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="quantity">Nombre de personnes</label><input required type="number" min="<?= (int)$order['min_people'] ?>" max="500" class="form-control" id="quantity" name="quantity" value="<?= htmlspecialchars((string)$data['quantity']) ?>"></div>
        </div>
        <hr>
        <h2 class="h5">Contact préalable du client</h2>
        <div class="mb-3"><label class="form-label" for="contact_method">Moyen utilisé</label><select required class="form-select" id="contact_method" name="contact_method"><option value="">Choisir</option><option value="phone" <?= $contactMethod === 'phone' ? 'selected' : '' ?>>Téléphone</option><option value="email" <?= $contactMethod === 'email' ? 'selected' : '' ?>>E-mail</option></select></div>
        <div class="mb-3"><label class="form-label" for="note">Motif de la modification</label><textarea required maxlength="500" class="form-control" id="note" name="note" rows="2"><?= htmlspecialchars($note) ?></textarea></div>
        <label><input type="checkbox" name="contacted" value="1" required> J’ai contacté le client avant cette modification.</label><br>
        <button class="btn btn-primary mt-4" type="submit">Enregistrer la modification</button>
      </form>
    </div></section>
    <aside class="col-lg-5"><div class="order-summary">
      <h2 class="h4">Nouveau total estimé</h2>
      <dl><div><dt>Prix des menus</dt><dd id="menu-total">—</dd></div><div><dt>Remise</dt><dd id="discount">—</dd></div><div><dt>Livraison</dt><dd id="delivery-fee">—</dd></div><div class="total"><dt>Total</dt><dd id="total">—</dd></div></dl>
    </div></aside>
  </div>
</main>
<script src="assets/order.js"></script>
<?php pageFooter(); ?>
