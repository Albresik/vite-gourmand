<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';

$title = '';
$email = currentUser()['email'] ?? '';
$message = '';
$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');

    if ($title === '' || $email === '' || $message === '') {
        $errors[] = 'Tous les champs sont obligatoires.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'L’adresse e-mail est invalide.';
    }
    if (mb_strlen($title) > 150 || mb_strlen($message) > 5000) {
        $errors[] = 'Le titre ou le message est trop long.';
    }

    if (!$errors) {
        try {
            $request = database()->prepare(
                'INSERT INTO contact_messages (title, email, message) VALUES (:title, :email, :message)'
            );
            $request->execute([
                'title' => $title,
                'email' => $email,
                'message' => $message,
            ]);
            $success = true;
            $title = '';
            $message = '';
        } catch (PDOException $error) {
            $errors[] = 'Le message n’a pas pu être enregistré. Réessaie plus tard.';
        }
    }
}

pageHeader('Contact');
?>
<main id="contenu" class="container py-5">
  <div class="auth-card mx-auto">
    <p class="eyebrow">Une question ?</p>
    <h1 class="h2">Contacter Vite &amp; Gourmand</h1>
    <p>Écris-nous au sujet d’un menu ou d’une prestation.</p>

    <?php if ($success): ?>
      <div class="alert alert-success" role="status">Votre demande a été enregistrée.</div>
    <?php endif; ?>
    <?php foreach ($errors as $error): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div>
    <?php endforeach; ?>

    <form method="post">
      <div class="mb-3">
        <label class="form-label" for="title">Objet</label>
        <input class="form-control" id="title" name="title" maxlength="150" required value="<?= htmlspecialchars($title) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="email">Votre e-mail</label>
        <input class="form-control" id="email" name="email" type="email" required value="<?= htmlspecialchars($email) ?>">
      </div>
      <div class="mb-3">
        <label class="form-label" for="message">Votre message</label>
        <textarea class="form-control" id="message" name="message" rows="6" maxlength="5000" required><?= htmlspecialchars($message) ?></textarea>
      </div>
      <button class="btn btn-primary" type="submit">Envoyer ma demande</button>
    </form>
  </div>
</main>
<?php pageFooter(); ?>
