<?php
// Usage local : C:\xampp\php\php.exe scripts\create_admin.php
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/auth.php';

function ask(string $question): string
{
    echo $question . ' : ';
    return trim(fgets(STDIN));
}

$firstName = ask('Prénom administrateur');
$lastName = ask('Nom administrateur');
$email = ask('E-mail administrateur');
$password = ask('Mot de passe initial (visible pendant la saisie)');

if ($firstName === '' || $lastName === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || !passwordIsValid($password)) {
    exit("Informations invalides. Mot de passe : 10 caractères, majuscule, minuscule, chiffre et caractère spécial.\n");
}

try {
    $request = database()->prepare('INSERT INTO users (first_name, last_name, email, phone, address, password_hash, role) VALUES (:first_name, :last_name, :email, "", "", :password_hash, "admin")');
    $request->execute(['first_name' => $firstName, 'last_name' => $lastName, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
    echo "Compte administrateur créé.\n";
} catch (PDOException $error) {
    echo "Compte non créé : e-mail déjà utilisé ou base indisponible.\n";
}
