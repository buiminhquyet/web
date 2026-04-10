<?php
require_once '../config.php';
require_once '../database.php';
require_once '../functions.php';

error_reporting(0);
ob_clean();
header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if (!$product_id) {
    echo json_encode([]);
    exit;
}

$packages = $db->query("SELECT * FROM packages WHERE product_id = '$product_id' ORDER BY price ASC");

$results = [];
while ($row = $packages->fetch_assoc()) {
    $results[] = [
        'id' => (int)$row['id'],
        'name' => htmlspecialchars($row['name']),
        'price' => (float)$row['price'],
        'stock' => (int)$row['stock'],
        'service_type' => htmlspecialchars($row['service_type'])
    ];
}

echo json_encode($results);
?>
