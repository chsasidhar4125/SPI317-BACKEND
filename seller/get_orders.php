<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (empty($_GET['seller_id'])) {
    echo json_encode(["success"=>false,"message"=>"Seller ID required"]);
    exit;
}

$seller_id = (int)$_GET['seller_id'];

$result = $conn->query(
    "SELECT order_code, customer_name, product_name,
            quantity, total_price, status, created_at
     FROM orders
     WHERE seller_id=$seller_id
     ORDER BY created_at DESC
     LIMIT 10"
);

$orders = [];
while ($row = $result->fetch_assoc()) {
    $orders[] = $row;
}

echo json_encode([
    "success"=>true,
    "orders"=>$orders
]);
