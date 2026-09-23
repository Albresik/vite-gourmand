<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['user']);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmation = $_POST['password_confirmation'] ?? '';

    $request = database()->prepare('SELECT password_hash FROM users WHERE id = :id');
    $request->execute(['id' => currentUser()['id']]);
    $user = $request->fetch();

    if (!$user || !password_verify($currentPassword, $user['password_hash'])) {
        $errors[] = 'Le mot de passe actuel est incorrect.';
    }
    if (!passwordIsValid($newPassword)) {
        $errors[] = 'Le nouveau mot de passe doit contenir au moins 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
    }
    if ($newPassword === $currentPassword) {
        $errors[] = 'Choisis un nouveau mot de passe différent de l’ancien.';
    }
    if ($newPassword !== $confirmation) {
        $errors[] = 'La confirmation ne correspond pas au nouveau mot de passe.';
    }

    if (!$errors) {
        $update = database()->prepare('UPDATE users SET password_hash = :password_hash WHERE id = :id');
        $update->execute([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
            'id' => currentUser()['id'],
        ]);
        header('Location: account.php?password_saved=1');
        exit;
    }
}

pageHeader('Changer mon mot de passe');
?>
<main id="contenu" class="container py-5">
  <a href="account.php">← Retour à mon espace</a>
  <div class="auth-card mx-auto mt-3">
    <h1 class="h2">Changer mon mot de passe</h1>
    <p>Choisis un mot de passe différent de celui que tu utilises actuellement.</p>
    <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
    <form method="post">
      <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>">
      <div class="mb-3"><label class="form-label" for="current_password">Mot de passe actuel</label><input required type="password" autocomplete="current-password" class="form-control" id="current_password" name="current_password"></div>
      <div class="mb-3"><label class="form-label" for="new_password">Nouveau mot de passe</label><input required type="password" autocomplete="new-password" class="form-control" id="new_password" name="new_password" aria-describedby="password-help"><div id="password-help" class="form-text">10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.</div></div>
      <div class="mb-3"><label class="form-label" for="password_confirmation">Confirmer le nouveau mot de passe</label><input required type="password" autocomplete="new-password" class="form-control" id="password_confirmation" name="password_confirmation"></div>
      <button class="btn btn-primary" type="submit">Enregistrer le nouveau mot de passe</button>
    </form>
  </div>
</main>
<?php pageFooter(); ?>
