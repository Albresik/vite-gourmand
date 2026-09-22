<?php
require_once __DIR__ . '/../config/database.php';

function database(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        // Aiven fournit un certificat CA à déposer hors du dépôt Git.
        $caFile = getenv('DB_SSL_CA');
        if ($caFile) {
            if (!is_readable($caFile)) throw new RuntimeException('Certificat MySQL introuvable.');
            $options[PDO::MYSQL_ATTR_SSL_CA] = $caFile;
            $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
        }
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASSWORD,
            $options
        );
    }
    return $pdo;
}
