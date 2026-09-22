<?php
// Test de connexion sans modification des données.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if (!getenv('DB_HOST') || !getenv('DB_NAME') || !getenv('DB_USER') || !getenv('DB_SSL_CA')) {
    fwrite(STDERR, "Configuration Aiven ou certificat manquant.\n");
    exit(1);
}
require_once __DIR__ . '/../includes/database.php';
if (in_array(strtolower(DB_HOST), ['localhost', '127.0.0.1', '::1'], true)) {
    fwrite(STDERR, "Ce test attend une base distante.\n");
    exit(1);
}
try {
    $tables = database()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "Connexion sécurisée réussie. Tables présentes : " . count($tables) . ".\n";
} catch (Throwable $error) {
    fwrite(STDERR, "Connexion impossible. Vérifie les valeurs Aiven et le certificat CA.\n");
    exit(1);
}
