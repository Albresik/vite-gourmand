<?php
// À lancer une seule fois sur une base distante vide, depuis le terminal local.
if (PHP_SAPI !== 'cli') { http_response_code(403); exit; }
if (!getenv('JAWSDB_URL') && !getenv('JAWSDB_MARIA_URL') &&
    (!getenv('DB_HOST') || !getenv('DB_NAME') || !getenv('DB_USER'))) {
    exit("Base distante non configurée. Import annulé.\n");
}
require_once __DIR__ . '/../includes/database.php';
if (in_array(strtolower(DB_HOST), ['localhost', '127.0.0.1', '::1'], true)) {
    exit("Import distant refusé sur une base locale.\n");
}

$existingTables = database()->query('SHOW TABLES')->fetchAll();
if ($existingTables) exit("La base contient déjà des tables. Import annulé pour éviter les doublons.\n");

$files = [
    'schema.sql', 'upgrade_auth.sql', 'upgrade_orders.sql',
    'upgrade_contact.sql', 'upgrade_employee.sql',
    'upgrade_menu_content.sql', 'upgrade_reviews.sql',
    'upgrade_hours.sql', 'upgrade_order_contacts.sql',
];

foreach ($files as $file) {
    $sql = file_get_contents(__DIR__ . '/../database/' . $file);
    if ($sql === false) exit("Fichier introuvable : $file\n");
    $sql = preg_replace('/^\s*(CREATE DATABASE|USE)\b[^;]*;\s*$/mi', '', $sql);
    $sql = preg_replace('/^\s*--[^\r\n]*$/m', '', $sql);
    foreach (explode(';', $sql) as $statement) {
        $statement = trim($statement);
        if ($statement !== '') database()->exec($statement);
    }
    echo "Importé : $file\n";
}
echo "Base de démonstration prête.\n";
