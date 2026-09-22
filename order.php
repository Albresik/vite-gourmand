<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['user']);

$userRequest = database()->prepare('SELECT * FROM users WHERE id = :id');
$userRequest->execute(['id' => currentUser()['id']]);
$user = $userRequest->fetch();

$menuId = filter_input(INPUT_GET, 'menu_id', FILTER_VALIDATE_INT)
    ?: filter_input(INPUT_POST, 'menu_id', FILTER_VALIDATE_INT);
$menuRequest = database()->prepare('SELECT * FROM menus WHERE id = :id AND stock > 0');
$menuRequest->execute(['id' => $menuId]);
$menu = $menuRequest->fetch();
if (!$menu) { header('Location: index.php#menus'); exit; }

$minDate = (new DateTimeImmutable('today'))->modify('+' . (int)$menu['lead_days'] . ' days')->format('Y-m-d');
$data = [
    'delivery_address' => $user['address'],
    'delivery_date' => '',
    'delivery_time' => '',
    'distance_km' => '0',
    'quantity' => (string)$menu['min_people'],
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($data as $field => $default) {
        $data[$field] = trim((string)($_POST[$field] ?? $default));
    }
    $quantity = filter_var($data['quantity'], FILTER_VALIDATE_INT);
    $distance = is_numeric($data['distance_km']) ? (float)$data['distance_km'] : -1;
    $inBordeaux = stripos($data['delivery_address'], 'bordeaux') !== false;

    if ($data['delivery_address'] === '' || $data['delivery_date'] === '' || $data['delivery_time'] === '') {
        $errors[] = 'Complète les informations de livraison.';
    }
    if ($quantity === false || $quantity < $menu['min_people'] || $quantity > 500) {
        $errors[] = 'Le nombre de personnes doit être compris entre ' . $menu['min_people'] . ' et 500.';
    }
    $parsedDate = DateTimeImmutable::createFromFormat('!Y-m-d', $data['delivery_date']);
    if (!$parsedDate || $parsedDate->format('Y-m-d') !== $data['delivery_date'] || $data['delivery_date'] < $minDate) {
        $errors[] = 'La date doit respecter le délai de commande du menu.';
    }
    if (!preg_match('/^([01][0-9]|2[0-3]):[0-5][0-9]$/', $data['delivery_time'])) {
        $errors[] = 'L’heure de livraison est invalide.';
    }
    if ($distance < 0 || $distance > 1000 || (!$inBordeaux && $distance == 0)) {
        $errors[] = 'Indique une distance positive pour une livraison hors Bordeaux.';
    }

    if (!$errors) {
        $menuTotal = round((float)$menu['price'] * $quantity, 2);
        $discount = $quantity >= $menu['min_people'] + 5 ? round($menuTotal * 0.10, 2) : 0;
        $delivery = $inBordeaux ? 5 : round(5 + 0.59 * $distance, 2);
        $total = $menuTotal - $discount + $delivery;

        try {
            database()->beginTransaction();
            $stock = database()->prepare('UPDATE menus SET stock = stock - 1 WHERE id = :id AND stock > 0');
            $stock->execute(['id' => $menu['id']]);
            if ($stock->rowCount() !== 1) throw new RuntimeException('Menu épuisé.');

            $insert = database()->prepare(
                'INSERT INTO customer_orders
                (user_id, menu_id, quantity, delivery_address, delivery_date, delivery_time,
                 distance_km, menu_total, discount_amount, delivery_fee, total_amount, status)
                VALUES
                (:user_id, :menu_id, :quantity, :delivery_address, :delivery_date, :delivery_time,
                 :distance_km, :menu_total, :discount_amount, :delivery_fee, :total_amount, :status)'
            );
            $insert->execute([
                'user_id' => $user['id'], 'menu_id' => $menu['id'], 'quantity' => $quantity,
                'delivery_address' => $data['delivery_address'],
                'delivery_date' => $data['delivery_date'],
                'delivery_time' => $data['delivery_time'],
                'distance_km' => $distance, 'menu_total' => $menuTotal,
                'discount_amount' => $discount, 'delivery_fee' => $delivery,
                'total_amount' => $total, 'status' => 'pending',
            ]);
            $orderId = database()->lastInsertId();
            $history = database()->prepare('INSERT INTO order_status_history (order_id, status) VALUES (:order_id, :status)');
            $history->execute(['order_id' => $orderId, 'status' => 'pending']);
            database()->commit();
            header('Location: order-success.php?id=' . $orderId);
            exit;
        } catch (Throwable $error) {
            if (database()->inTransaction()) database()->rollBack();
            $errors[] = 'La commande n’a pas pu être enregistrée.';
        }
    }
}

pageHeader('Commander');
?>
<main id="contenu" class="container py-5">
  <p class="eyebrow">Commande</p>
  <h1>Finaliser votre prestation</h1>
  <div class="row g-4">
    <section class="col-lg-7"><div class="order-panel">
      <h2 class="h4">Informations de livraison</h2>
      <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
      <form method="post" id="order-form" data-price="<?= htmlspecialchars($menu['price']) ?>" data-min="<?= (int)$menu['min_people'] ?>">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
        <input type="hidden" name="menu_id" value="<?= (int)$menu['id'] ?>">
        <div class="row g-3">
          <div class="col-md-6"><label class="form-label" for="customer_name">Nom</label><input readonly class="form-control" id="customer_name" value="<?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="email">E-mail</label><input readonly class="form-control" id="email" value="<?= htmlspecialchars($user['email']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="phone">Téléphone</label><input readonly class="form-control" id="phone" value="<?= htmlspecialchars($user['phone']) ?>"></div>
          <div class="col-12"><label class="form-label" for="delivery_address">Adresse de prestation</label><input required class="form-control" name="delivery_address" id="delivery_address" value="<?= htmlspecialchars($data['delivery_address']) ?>"></div>
          <div class="col-md-4"><label class="form-label" for="delivery_date">Date</label><input required type="date" class="form-control" name="delivery_date" id="delivery_date" min="<?= $minDate ?>" value="<?= htmlspecialchars($data['delivery_date']) ?>"></div>
          <div class="col-md-4"><label class="form-label" for="delivery_time">Heure souhaitée</label><input required type="time" class="form-control" name="delivery_time" id="delivery_time" value="<?= htmlspecialchars($data['delivery_time']) ?>"></div>
          <div class="col-md-4"><label class="form-label" for="distance_km">Distance hors Bordeaux (km)</label><input required type="number" min="0" step="0.1" class="form-control" name="distance_km" id="distance_km" value="<?= htmlspecialchars($data['distance_km']) ?>"></div>
          <div class="col-md-6"><label class="form-label" for="quantity">Nombre de personnes</label><input required type="number" min="<?= (int)$menu['min_people'] ?>" class="form-control" name="quantity" id="quantity" value="<?= htmlspecialchars($data['quantity']) ?>"></div>
        </div>
        <p class="form-text">Pour une adresse hors Bordeaux, renseigne la distance depuis Bordeaux. Le total sera recalculé avant confirmation.</p>
        <button class="btn btn-primary mt-3" type="submit">Confirmer la commande</button>
      </form>
    </div></section>
    <aside class="col-lg-5"><div class="order-summary">
      <h2 class="h4">Récapitulatif</h2>
      <p><strong><?= htmlspecialchars($menu['title']) ?></strong><br>Minimum <?= (int)$menu['min_people'] ?> personnes · <?= number_format($menu['price'], 2, ',', ' ') ?> € / personne</p>
      <dl>
        <div><dt>Prix des menus</dt><dd id="menu-total">—</dd></div>
        <div><dt>Remise (10 % dès <?= (int)$menu['min_people'] + 5 ?> personnes)</dt><dd id="discount">—</dd></div>
        <div><dt>Livraison</dt><dd id="delivery-fee">—</dd></div>
        <div class="total"><dt>Total</dt><dd id="total">—</dd></div>
      </dl>
    </div></aside>
  </div>
</main>
<script src="assets/order.js"></script>
<?php pageFooter(); ?>
