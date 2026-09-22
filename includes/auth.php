<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function isLoggedIn(): bool { return isset($_SESSION['user']); }
function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function requireLogin(): void { if (!isLoggedIn()) { header('Location: login.php'); exit; } }
function requireRole(array $allowedRoles): void
{
    requireLogin();
    require_once __DIR__ . '/database.php';
    $request = database()->prepare('SELECT role, is_active FROM users WHERE id = :id');
    $request->execute(['id' => currentUser()['id']]);
    $account = $request->fetch();
    if (!$account || !$account['is_active']) {
        unset($_SESSION['user']);
        header('Location: login.php');
        exit;
    }
    $_SESSION['user']['role'] = $account['role'];
    if (!in_array($account['role'], $allowedRoles, true)) {
        http_response_code(403);
        exit('Accès refusé.');
    }
}
function csrfToken(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}
function verifyCsrf(): void
{
    if (!hash_equals(csrfToken(), $_POST['csrf_token'] ?? '')) {
        http_response_code(400);
        exit('Formulaire expiré. Rechargez la page et réessayez.');
    }
}
function passwordIsValid(string $password): bool
{
    return strlen($password) >= 10
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[^a-zA-Z\d]/', $password);
}
