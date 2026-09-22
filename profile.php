<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['user']);

$userId = (int)currentUser()['id'];
$request = database()->prepare('SELECT first_name, last_name, email, phone, address, password_hash FROM users WHERE id = :id');
$request->execute(['id' => $userId]);
$user = $request->fetch();
$data = [
    'first_name' => $user['first_name'], 'last_name' => $user['last_name'],
    'email' => $user['email'], 'phone' => $user['phone'], 'address' => $user['address'],
];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    foreach ($data as $field => $value) {
        $data[$field] = trim((string)($_POST[$field] ?? ''));
    }
    if (in_array('', $data, true)) $errors[] = 'Tous les champs sont obligatoires.';
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) $errors[] = 'L’adresse e-mail est invalide.';
    if (strlen($data['first_name']) > 80 || strlen($data['last_name']) > 80 || strlen($data['email']) > 190 || strlen($data['phone']) > 30 || strlen($data['address']) > 255) {
        $errors[] = 'Un des champs est trop long.';
    }
    if (!password_verify($_POST['current_password'] ?? '', $user['password_hash'])) {
        $errors[] = 'Le mot de passe actuel est incorrect.';
    }

    if (!$errors) {
        try {
            $update = database()->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, phone = :phone, address = :address WHERE id = :id');
            $update->execute([...$data, 'id' => $userId]);
            $_SESSION['user']['first_name'] = $data['first_name'];
            $_SESSION['user']['last_name'] = $data['last_name'];
            $_SESSION['user']['email'] = $data['email'];
            header('Location: account.php?profile_saved=1'); exit;
        } catch (PDOException $exception) {
            $errors[] = $exception->getCode() === '23000'
                ? 'Cette adresse e-mail est déjà utilisée.'
                : 'Les informations n’ont pas pu être enregistrées.';
        }
    }
}

pageHeader('Modifier mon profil');
?>
<main id="contenu" class="container py-5">
  <a href="account.php">← Retour à mon espace</a>
  <div class="auth-card mx-auto mt-3">
    <h1 class="h2">Modifier mon profil</h1>
    <p>Confirme ton mot de passe actuel avant d’enregistrer tes changements.</p>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="row g-3">
        <div class="col-md-6"><label class="form-label" for="first_name">Prénom</label><input required maxlength="80" class="form-control" id="first_name" name="first_name" value="<?= htmlspecialchars($data['first_name']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="last_name">Nom</label><input required maxlength="80" class="form-control" id="last_name" name="last_name" value="<?= htmlspecialchars($data['last_name']) ?>"></div>
        <div class="col-12"><label class="form-label" for="email">E-mail</label><input required type="email" maxlength="190" class="form-control" id="email" name="email" value="<?= htmlspecialchars($data['email']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="phone">Téléphone</label><input required type="tel" maxlength="30" class="form-control" id="phone" name="phone" value="<?= htmlspecialchars($data['phone']) ?>"></div>
        <div class="col-md-6"><label class="form-label" for="address">Adresse postale</label><input required maxlength="255" class="form-control" id="address" name="address" value="<?= htmlspecialchars($data['address']) ?>"></div>
        <div class="col-12"><label class="form-label" for="current_password">Mot de passe actuel</label><input required type="password" autocomplete="current-password" class="form-control" id="current_password" name="current_password"></div>
      </div>
      <button class="btn btn-primary mt-4" type="submit">Enregistrer mon profil</button>
    </form>
  </div>
</main>
<?php pageFooter(); ?>
