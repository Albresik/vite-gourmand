<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
$errors = []; $data = ['first_name' => '', 'last_name' => '', 'email' => '', 'phone' => '', 'address' => ''];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($data as $field => $value) { $data[$field] = trim($_POST[$field] ?? ''); }
    $password = $_POST['password'] ?? ''; $confirm = $_POST['password_confirmation'] ?? '';
    if (in_array('', $data, true)) $errors[] = 'Tous les champs sont obligatoires.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'L’adresse e-mail est invalide.';
    if (!passwordIsValid($password)) $errors[] = 'Le mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
    if ($password !== $confirm) $errors[] = 'Les mots de passe ne correspondent pas.';
    if (!$errors) {
        try {
            $statement = database()->prepare('INSERT INTO users (first_name, last_name, email, phone, address, password_hash, role) VALUES (:first_name, :last_name, :email, :phone, :address, :password_hash, "user")');
            $statement->execute([...$data, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php?registered=1'); exit;
        } catch (PDOException $exception) { $errors[] = $exception->getCode() === '23000' ? 'Cette adresse e-mail est déjà utilisée.' : 'Une erreur est survenue. Réessaie plus tard.'; }
    }
}
pageHeader('Créer un compte'); ?>
<main id="contenu" class="container py-5"><div class="auth-card mx-auto"><p class="eyebrow">Nouveau client</p><h1 class="h2">Créer un compte</h1><p>Tu pourras commander et suivre tes prestations.</p><?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?><form method="post" novalidate><div class="row g-3"><div class="col-md-6"><label for="first_name" class="form-label">Prénom</label><input required class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($data['first_name']) ?>"></div><div class="col-md-6"><label for="last_name" class="form-label">Nom</label><input required class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($data['last_name']) ?>"></div><div class="col-12"><label for="email" class="form-label">Adresse e-mail</label><input required type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($data['email']) ?>"></div><div class="col-md-6"><label for="phone" class="form-label">Numéro de GSM</label><input required type="tel" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($data['phone']) ?>"></div><div class="col-md-6"><label for="address" class="form-label">Adresse postale</label><input required class="form-control" id="address" name="address" value="<?= htmlspecialchars($data['address']) ?>"></div><div class="col-md-6"><label for="password" class="form-label">Mot de passe</label><input required type="password" class="form-control" id="password" name="password" aria-describedby="password-help"><div id="password-help" class="form-text">10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.</div></div><div class="col-md-6"><label for="password_confirmation" class="form-label">Confirmer le mot de passe</label><input required type="password" class="form-control" id="password_confirmation" name="password_confirmation"></div></div><button class="btn btn-primary mt-4" type="submit">Créer mon compte</button></form><p class="mt-4 mb-0">Déjà inscrit ? <a href="login.php">Se connecter</a></p></div></main><?php pageFooter(); ?>
