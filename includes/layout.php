<?php
require_once __DIR__ . '/auth.php';
function pageHeader(string $title): void { ?>
<!doctype html><html lang="fr"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="description" content="Vite & Gourmand, traiteur à Bordeaux."><title><?= htmlspecialchars($title) ?> | Vite & Gourmand</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"><link href="assets/style.css" rel="stylesheet"></head><body>
<a class="visually-hidden-focusable skip-link" href="#contenu">Aller au contenu principal</a><header class="border-bottom bg-white sticky-top"><nav class="navbar navbar-expand-lg container py-3" aria-label="Navigation principale"><a class="navbar-brand fw-bold" href="index.php">Vite <span>&amp; Gourmand</span></a><button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navigation" aria-controls="navigation" aria-expanded="false" aria-label="Ouvrir la navigation"><span class="navbar-toggler-icon"></span></button><div class="collapse navbar-collapse" id="navigation"><ul class="navbar-nav ms-auto gap-lg-2"><li class="nav-item"><a class="nav-link" href="index.php#menus">Nos menus</a></li><li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li><?php if (isLoggedIn()): ?><li class="nav-item"><a class="nav-link" href="<?= currentUser()['role'] === 'admin' ? 'admin.php' : (currentUser()['role'] === 'employee' ? 'employee.php' : 'account.php') ?>">Mon espace</a></li><li class="nav-item"><a class="btn btn-dark ms-lg-2" href="logout.php">Déconnexion</a></li><?php else: ?><li class="nav-item"><a class="btn btn-dark ms-lg-2" href="login.php">Connexion</a></li><?php endif; ?></ul></div></nav></header>
<?php }
function pageFooter(): void {
    require_once __DIR__ . '/database.php';
    $hours = database()->query('SELECT * FROM opening_hours ORDER BY day_of_week')->fetchAll();
    $days = [1 => 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi', 'Dimanche'];
?>
<footer class="footer mt-5 py-5"><div class="container row g-4">
  <div class="col-md-6"><h2 class="h5">Vite &amp; Gourmand</h2><p>Traiteur bordelais depuis 25 ans, pour vos repas et événements.</p></div>
  <div class="col-md-3"><h2 class="h6">Horaires</h2><ul class="list-unstyled mb-0">
    <?php foreach ($hours as $hour): ?><li><?= $days[$hour['day_of_week']] ?> : <?= $hour['is_closed'] ? 'Fermé' : htmlspecialchars(substr($hour['opening_time'], 0, 5) . ' – ' . substr($hour['closing_time'], 0, 5)) ?></li><?php endforeach; ?>
  </ul></div>
  <div class="col-md-3"><h2 class="h6">Informations</h2><a href="mentions-legales.php">Mentions légales</a><br><a href="cgv.php">Conditions générales de vente</a></div>
</div></footer><script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script><script src="assets/app.js"></script></body></html>
<?php }
