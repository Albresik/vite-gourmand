<?php
// Usage distant : php scripts/create_admin.php --remote (après configuration DB_*).
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';
if (in_array('--remote', $argv ?? [], true) &&
    (in_array(strtolower(DB_HOST), ['localhost', '127.0.0.1', '::1'], true) || !getenv('DB_SSL_CA'))) {
    fwrite(STDERR, "Base distante sécurisée non configurée. Création annulée.\n");
    exit(1);
}

function ask(string $question): string
{
    echo $question . ' : ';
    return trim(fgets(STDIN));
}

$firstName = ask('Prénom administrateur');
$lastName = ask('Nom administrateur');
$email = ask('E-mail administrateur');
$password = getenv('ADMIN_PASSWORD') ?: ask('Mot de passe initial (visible pendant la saisie)');

if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !passwordIsValid($password)) {
    exit("Informations invalides. Mot de passe : 10 caractères, majuscule, minuscule, chiffre et caractère spécial.\n");
}

try {
    $request = database()->prepare('INSERT INTO users (first_name, last_name, email, phone, address, password_hash, role) VALUES (:first_name, :last_name, :email, :phone, :address, :password_hash, :role)');
    $request->execute([
        'first_name' => $firstName,
        'last_name' => $lastName,
        'email' => $email,
        'phone' => '',
        'address' => '',
        'password_hash' => password_hash($password, PASSWORD_DEFAULT),
        'role' => 'admin',
    ]);
    echo "Compte administrateur créé.\n";
} catch (PDOException $error) {
    if (($error->errorInfo[1] ?? null) === 1062) {
        fwrite(STDERR, "Compte non créé : cet e-mail existe déjà.\n");
    } else {
        fwrite(STDERR, "Compte non créé : erreur SQL " . $error->getCode() . " (code " . ($error->errorInfo[1] ?? '?') . ").\n");
    }
    exit(1);
}
