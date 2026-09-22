<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
require_once __DIR__ . '/includes/mongo.php';
requireRole(['admin']);

$menus = database()->query('SELECT id, title FROM menus ORDER BY title')->fetchAll();
$selectedMenu = filter_input(INPUT_GET, 'menu_id', FILTER_VALIDATE_INT) ?: 0;
$start = trim($_GET['start'] ?? '');
$end = trim($_GET['end'] ?? '');
$error = '';
$stats = [];
$totalRevenue = 0;

$startDate = $start !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $start) : null;
$endDate = $end !== '' ? DateTimeImmutable::createFromFormat('!Y-m-d', $end) : null;
if ($start !== '' && (!$startDate || $startDate->format('Y-m-d') !== $start)) $error = 'Date de début invalide.';
if ($end !== '' && (!$endDate || $endDate->format('Y-m-d') !== $end)) $error = 'Date de fin invalide.';
if ($start && $end && $start > $end) $error = 'La date de début doit précéder la date de fin.';

if (!$error) {
    try {
        $collection = mongoOrdersCollection();
        if (!$collection) {
            $error = 'MongoDB n’est pas encore configuré sur ce serveur.';
        } else {
            syncOrdersToMongo($collection);
            $filter = ['status' => ['$ne' => 'cancelled']];
            if ($selectedMenu) $filter['menu_id'] = $selectedMenu;
            if ($start || $end) {
                $filter['order_date'] = [];
                if ($start) $filter['order_date']['$gte'] = $start;
                if ($end) $filter['order_date']['$lte'] = $end;
            }
            foreach ($collection->find($filter) as $order) {
                $id = (int)$order['menu_id'];
                if (!isset($stats[$id])) $stats[$id] = ['title' => (string)$order['menu_title'], 'count' => 0, 'revenue' => 0];
                $stats[$id]['count']++;
                if ($order['status'] === 'completed') {
                    $stats[$id]['revenue'] += (float)$order['total_amount'];
                    $totalRevenue += (float)$order['total_amount'];
                }
            }
            uasort($stats, fn($a, $b) => $b['count'] <=> $a['count']);
        }
    } catch (Throwable $exception) {
        $error = 'Connexion MongoDB impossible. Vérifie la configuration Atlas et l’accès réseau.';
    }
}

$maxCount = max([1, ...array_column($stats, 'count')]);
pageHeader('Statistiques');
?>
<main id="contenu" class="container py-5">
  <a href="admin.php">← Retour à l’administration</a>
  <h1 class="h2 mt-3">Statistiques des commandes</h1>
  <p>Les données du graphique proviennent de MongoDB. Les commandes annulées sont exclues ; le chiffre d’affaires compte seulement les commandes terminées.</p>
  <form method="get" class="row g-3 align-items-end mb-4">
    <div class="col-md-4"><label class="form-label" for="menu_id">Menu</label><select class="form-select" id="menu_id" name="menu_id"><option value="">Tous les menus</option><?php foreach ($menus as $menu): ?><option value="<?= (int)$menu['id'] ?>" <?= $selectedMenu === (int)$menu['id'] ? 'selected' : '' ?>><?= htmlspecialchars($menu['title']) ?></option><?php endforeach; ?></select></div>
    <div class="col-md-3"><label class="form-label" for="start">Du</label><input type="date" class="form-control" id="start" name="start" value="<?= htmlspecialchars($start) ?>"></div>
    <div class="col-md-3"><label class="form-label" for="end">Au</label><input type="date" class="form-control" id="end" name="end" value="<?= htmlspecialchars($end) ?>"></div>
    <div class="col-md-2"><button class="btn btn-outline-dark" type="submit">Filtrer</button></div>
  </form>
  <?php if ($error): ?><div class="alert alert-warning" role="alert"><?= htmlspecialchars($error) ?></div><?php endif; ?>
  <?php if (!$error): ?>
    <p class="lead">Chiffre d’affaires : <strong><?= number_format($totalRevenue, 2, ',', ' ') ?> €</strong></p>
    <?php if (!$stats): ?><p>Aucune commande pour ces filtres.</p><?php endif; ?>
    <?php foreach ($stats as $item): ?>
      <div class="order-panel mb-3">
        <div class="d-flex justify-content-between"><strong><?= htmlspecialchars($item['title']) ?></strong><span><?= (int)$item['count'] ?> commande(s)</span></div>
        <div class="progress my-2" role="img" aria-label="<?= htmlspecialchars($item['title']) ?> : <?= (int)$item['count'] ?> commandes"><div class="progress-bar" style="width: <?= (int)round($item['count'] * 100 / $maxCount) ?>%; background-color:#7c2938"></div></div>
        <small>Chiffre d’affaires terminé : <?= number_format($item['revenue'], 2, ',', ' ') ?> €</small>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</main>
<?php pageFooter(); ?>
