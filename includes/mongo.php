<?php
require_once __DIR__ . '/database.php';

function mongoOrdersCollection()
{
    $uri = getenv('MONGODB_URI');
    if (!$uri || !extension_loaded('mongodb')) return null;

    require_once __DIR__ . '/../vendor/autoload.php';
    $client = new MongoDB\Client($uri, ['serverSelectionTimeoutMS' => 3000]);
    $databaseName = getenv('MONGODB_DB') ?: (getenv('DYNO') ? 'vite_gourmand_prod' : 'vite_gourmand_local');
    return $client->selectCollection($databaseName, 'order_stats');
}

function syncOrdersToMongo($collection): void
{
    // Petite application de démonstration : on recopie les montants utiles au graphique.
    // Aucune donnée personnelle du client n'est envoyée dans MongoDB.
    $orders = database()->query(
        'SELECT o.id, o.menu_id, o.total_amount, o.status, o.created_at, m.title
         FROM customer_orders o JOIN menus m ON m.id = o.menu_id'
    )->fetchAll();

    $collection->createIndex(['order_id' => 1], ['unique' => true]);
    $ids = [];
    foreach ($orders as $order) {
        $id = (int)$order['id'];
        $ids[] = $id;
        $collection->replaceOne(['order_id' => $id], [
            'order_id' => $id,
            'menu_id' => (int)$order['menu_id'],
            'menu_title' => $order['title'],
            'order_date' => substr($order['created_at'], 0, 10),
            'status' => $order['status'],
            'total_amount' => (float)$order['total_amount'],
        ], ['upsert' => true]);
    }
    $collection->deleteMany(['order_id' => ['$nin' => $ids]]);
}
