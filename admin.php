<?php
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/layout.php';
requireRole(['admin']);

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrf();
    $action = $_POST['action'] ?? '';
    if ($action === 'create') {
        $firstName = trim($_POST['first_name'] ?? '');
        $lastName = trim($_POST['last_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Renseigne le nom, le prénom et un e-mail valide.';
        if (!passwordIsValid($password)) $errors[] = 'Le mot de passe doit avoir 10 caractères, une majuscule, une minuscule, un chiffre et un caractère spécial.';
        if (!$errors) {
            try {
                $request = database()->prepare('INSERT INTO users (first_name, last_name, email, phone, address, password_hash, role) VALUES (:first_name, :last_name, :email, "", "", :password_hash, "employee")');
                $request->execute(['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
                header('Location: admin.php?created=1'); exit;
            } catch (PDOException $error) { $errors[] = 'Impossible de créer ce compte. L’e-mail est peut-être déjà utilisé.'; }
        }
    } elseif ($action === 'toggle') {
        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        if ($id) {
            $request = database()->prepare('UPDATE users SET is_active = 1 - is_active WHERE id = :id AND role = "employee"');
            $request->execute(['id' => $id]);
            header('Location: admin.php?updated=1'); exit;
        }
    }
}

$employees = database()->query('SELECT id, first_name, last_name, email, is_active FROM users WHERE role = "employee" ORDER BY id DESC')->fetchAll();
pageHeader('Administration');
?>
<main id="contenu" class="container py-5">
  <p class="eyebrow">Administration</p><h1 class="h2">Espace administrateur</h1>
  <p><a class="btn btn-outline-dark" href="employee.php">Gérer les commandes et les menus</a></p>
  <?php if (isset($_GET['created'])): ?><div class="alert alert-success" role="status">Compte employé créé. Communique le mot de passe à la personne par un canal sûr.</div><?php endif; ?>
  <?php if (isset($_GET['updated'])): ?><div class="alert alert-success" role="status">Accès employé mis à jour.</div><?php endif; ?>
  <?php foreach ($errors as $error): ?><div class="alert alert-danger" role="alert"><?= htmlspecialchars($error) ?></div><?php endforeach; ?>
  <div class="row g-4">
    <section class="col-lg-6"><div class="order-panel"><h2 class="h4">Créer un employé</h2>
      <form method="post">
        <input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="create">
        <div class="row g-3"><div class="col-md-6"><label class="form-label" for="first_name">Prénom</label><input class="form-control" id="first_name" name="first_name" required></div><div class="col-md-6"><label class="form-label" for="last_name">Nom</label><input class="form-control" id="last_name" name="last_name" required></div></div>
        <div class="my-3"><label class="form-label" for="email">E-mail</label><input class="form-control" type="email" id="email" name="email" required></div>
        <div class="mb-3"><label class="form-label" for="password">Mot de passe initial</label><input class="form-control" type="password" id="password" name="password" required aria-describedby="password-help"><div class="form-text" id="password-help">10 caractères minimum, avec majuscule, minuscule, chiffre et caractère spécial.</div></div>
        <button class="btn btn-primary" type="submit">Créer le compte</button>
      </form>
    </div></section>
    <section class="col-lg-6"><h2 class="h4">Comptes employés</h2>
      <?php if (!$employees): ?><p>Aucun employé pour le moment.</p><?php endif; ?>
      <?php foreach ($employees as $employee): ?><div class="order-panel mb-2"><strong><?= htmlspecialchars($employee['first_name'] . ' ' . $employee['last_name']) ?></strong><p class="mb-2"><?= htmlspecialchars($employee['email']) ?> · <?= $employee['is_active'] ? 'Actif' : 'Désactivé' ?></p><form method="post"><input type="hidden" name="csrf_token" value="<?= csrfToken() ?>"><input type="hidden" name="action" value="toggle"><input type="hidden" name="id" value="<?= (int)$employee['id'] ?>"><button class="btn btn-sm btn-outline-dark" type="submit"><?= $employee['is_active'] ? 'Désactiver' : 'Réactiver' ?></button></form></div><?php endforeach; ?>
    </section>
  </div>
  <section class="order-panel mt-4"><h2 class="h4">Statistiques</h2><p class="mb-0">Le graphique des commandes et le chiffre d’affaires seront ajoutés après la connexion à MongoDB.</p></section>
</main>
<?php pageFooter(); ?>
