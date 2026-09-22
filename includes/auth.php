<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

function isLoggedIn(): bool { return isset($_SESSION['user']); }
function currentUser(): ?array { return $_SESSION['user'] ?? null; }
function requireLogin(): void { if (!isLoggedIn()) { header('Location: login.php'); exit; } }
function passwordIsValid(string $password): bool
{
    return strlen($password) >= 10
        && preg_match('/[A-Z]/', $password)
        && preg_match('/[a-z]/', $password)
        && preg_match('/\d/', $password)
        && preg_match('/[^a-zA-Z\d]/', $password);
}
