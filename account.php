<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin(); $user = currentUser();
if ($user['role'] === 'admin') { header('Location: admin.php'); exit; }
if ($user['role'] === 'employee') { header('Location: employee.php'); exit; }
$orders = database()->prepare('SELECT customer_orders.*, menus.title FROM customer_orders JOIN menus ON menus.id = customer_orders.menu_id WHERE customer_orders.user_id = :user_id ORDER BY customer_orders.created_at DESC');
$orders->execute(['user_id' => $user['id']]); $orders = $orders->fetchAll();
pageHeader('Mon espace'); ?>
<main id="contenu" class="container py-5"><p class="eyebrow">Espace <?= htmlspecialchars($user['role']) ?></p><h1 class="h2">Bonjour <?= htmlspecialchars($user['first_name']) ?> !</h1><div class="row g-4 mt-2"><section class="col-md-7"><div class="p-4 border rounded-3 bg-white"><h2 class="h4">Mes commandes</h2><?php if (!$orders): ?><p class="mb-0">Aucune commande pour le moment. <a href="index.php#menus">Découvrir les menus</a></p><?php else: ?><?php foreach ($orders as $order): ?><article class="border-top pt-3 mt-3"><div class="d-flex justify-content-between gap-3"><div><h3 class="h5 mb-1"><?= htmlspecialchars($order['title']) ?></h3><p class="mb-1">Commande n°<?= (int)$order['id'] ?> · <?= (int)$order['quantity'] ?> personnes · <?= htmlspecialchars($order['delivery_date']) ?></p><span class="badge text-bg-secondary"><?= htmlspecialchars($order['status']) ?></span></div><strong><?= number_format($order['total_amount'], 2, ',', ' ') ?> €</strong></div></article><?php endforeach; ?><?php endif; ?></div></section><aside class="col-md-5"><div class="p-4 border rounded-3 bg-white"><h2 class="h4">Mon profil</h2><dl class="mb-0"><dt>Nom</dt><dd><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></dd><dt>E-mail</dt><dd><?= htmlspecialchars($user['email']) ?></dd></dl></div></aside></div></main><?php pageFooter(); ?>
