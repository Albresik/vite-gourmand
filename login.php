<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = database()->prepare('SELECT id, first_name, last_name, email, password_hash, role, is_active FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => trim($_POST['email'] ?? '')]); $user = $statement->fetch();
    if (!$user || !$user['is_active'] || !password_verify($_POST['password'] ?? '', $user['password_hash'])) { $error = 'Adresse e-mail ou mot de passe incorrect.'; }
    else { session_regenerate_id(true); $_SESSION['user'] = ['id' => $user['id'], 'first_name' => $user['first_name'], 'last_name' => $user['last_name'], 'email' => $user['email'], 'role' => $user['role']]; header('Location: account.php'); exit; }
}
pageHeader('Connexion'); ?>
<main id="contenu" class="container py-5"><div class="auth-card mx-auto"><p class="eyebrow">Espace client</p><h1 class="h2">Connexion</h1><?php if (isset($_GET['registered'])): ?><div class="alert alert-success" role="alert">Compte créé. Tu peux maintenant te connecter.</div><?php endif; ?><?php if ($error): ?><div class="alert alert-danger" role="alert"><?= $error ?></div><?php endif; ?><form method="post"><div class="mb-3"><label class="form-label" for="email">Adresse e-mail</label><input class="form-control" type="email" name="email" id="email" required autocomplete="email"></div><div class="mb-3"><label class="form-label" for="password">Mot de passe</label><input class="form-control" type="password" name="password" id="password" required autocomplete="current-password"></div><button class="btn btn-primary" type="submit">Se connecter</button></form><p class="mt-4 mb-0">Pas encore de compte ? <a href="register.php">Créer un compte</a></p></div></main><?php pageFooter(); ?>
