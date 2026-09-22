<?php
// En local, XAMPP utilise les valeurs par défaut ci-dessous.
// En ligne, les variables DB_* configurent la base distante et DB_SSL_CA son certificat.
// JAWSDB_URL reste disponible comme autre possibilité.
$databaseUrl = getenv('JAWSDB_URL') ?: getenv('JAWSDB_MARIA_URL') ?: '';

if ($databaseUrl !== '') {
    $parts = parse_url($databaseUrl);
    if (!$parts || !isset($parts['host'], $parts['user'], $parts['path'])) {
        throw new RuntimeException('Configuration de la base de données invalide.');
    }
    define('DB_HOST', $parts['host']);
    define('DB_PORT', (int)($parts['port'] ?? 3306));
    define('DB_NAME', ltrim($parts['path'], '/'));
    define('DB_USER', rawurldecode($parts['user']));
    define('DB_PASSWORD', rawurldecode($parts['pass'] ?? ''));
} else {
    define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
    define('DB_PORT', (int)(getenv('DB_PORT') ?: 3306));
    define('DB_NAME', getenv('DB_NAME') ?: 'vite_gourmand');
    define('DB_USER', getenv('DB_USER') ?: 'root');
    define('DB_PASSWORD', getenv('DB_PASSWORD') ?: '');
}
