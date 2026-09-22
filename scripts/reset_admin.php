<?php
// À lancer uniquement depuis PowerShell avec les variables DB_* de la base distante.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

if (!in_array('--remote', $argv, true) ||
    in_array(strtolower(DB_HOST), ['localhost', '127.0.0.1', '::1'], true) ||
    !getenv('DB_SSL_CA')) {
    exit("Base distante sécurisée non configurée. Aucune modification effectuée.\n");
}

try {
    $request = database()->prepare('SELECT id, email, is_active FROM users WHERE role = :role ORDER BY id');
    $request->execute(['role' => 'admin']);
    $admins = $request->fetchAll();
} catch (Throwable $error) {
    exit("Connexion à la base distante impossible. Vérifie les variables DB_* et le certificat.\n");
}

if (!$admins) exit("Aucun compte administrateur dans cette base.\n");
foreach ($admins as $admin) {
    echo $admin['id'] . ' — ' . $admin['email'] . ' — ' . ($admin['is_active'] ? 'actif' : 'désactivé') . "\n";
}

if (!in_array('--reset', $argv, true)) {
    exit("Lecture seule. Pour changer un mot de passe, relance avec --remote --reset.\n");
}

$newPassword = getenv('ADMIN_PASSWORD_NEW') ?: '';
if (!passwordIsValid($newPassword)) {
    exit("Définis ADMIN_PASSWORD_NEW : 10 caractères minimum, majuscule, minuscule, chiffre et caractère spécial.\n");
}

echo 'Numéro (id) du compte à réinitialiser : ';
$id = filter_var(trim(fgets(STDIN)), FILTER_VALIDATE_INT);
$selected = null;
foreach ($admins as $admin) {
    if ((int)$admin['id'] === $id) { $selected = $admin; break; }
}
if (!$selected) exit("Identifiant invalide. Aucune modification effectuée.\n");

echo 'Confirmer le changement du mot de passe de ' . $selected['email'] . ' ? Écris oui : ';
if (trim(fgets(STDIN)) !== 'oui') exit("Annulé. Aucune modification effectuée.\n");

try {
    $request = database()->prepare('UPDATE users SET password_hash = :hash WHERE id = :id AND role = :role');
    $request->execute(['hash' => password_hash($newPassword, PASSWORD_DEFAULT), 'id' => $id, 'role' => 'admin']);
    echo "Mot de passe modifié. Connecte-toi avec l'e-mail affiché et le nouveau mot de passe.\n";
    if (!$selected['is_active']) echo "Attention : ce compte reste désactivé.\n";
} catch (Throwable $error) {
    exit("Modification impossible. Aucune confirmation de changement.\n");
}
