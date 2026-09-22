<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../includes/database.php';
try {
    $where = ['stock > 0']; $params = [];
    foreach (['max_price' => 'price <=', 'min_price' => 'price >=', 'people' => 'min_people <='] as $key => $condition) {
        if (isset($_GET[$key]) && $_GET[$key] !== '' && is_numeric($_GET[$key])) { $where[] = $condition . ' :' . $key; $params[$key] = (float) $_GET[$key]; }
    }
    foreach (['theme', 'diet'] as $key) { if (!empty($_GET[$key])) { $where[] = $key . ' = :' . $key; $params[$key] = $_GET[$key]; } }
    $query = 'SELECT id, title, description, theme, diet, min_people, price, stock, image_url FROM menus WHERE ' . implode(' AND ', $where) . ' ORDER BY price';
    $statement = database()->prepare($query); $statement->execute($params); echo json_encode($statement->fetchAll());
} catch (Throwable $error) { http_response_code(500); echo json_encode(['error' => 'Impossible de charger les menus. Vérifiez la base de données.']); }
