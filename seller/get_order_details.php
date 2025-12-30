<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (empty($_GET['order_id'])) {
    echo json_encode(["success"=>false,"message"=>"Order ID required"]);
    exit;
}

$order_id = (int)$_GET['order_id'];

$order = $conn->query(
    "SELECT order_code, customer_name, customer_phone,
            delivery_address, status
     FROM orders WHERE id=$order_id"
)->fetch_assoc();

$itemsRes = $conn->query(
    "SELECT product_name, quantity, price
     FROM order_items WHERE order_id=$order_id"
);

$items = [];
$total = 0;
while ($row = $itemsRes->fetch_assoc()) {
    $items[] = $row;
    $total += $row['price'];
}

echo json_encode([
    "success" => true,
    "order" => $order,
    "items" => $items,
    "total_amount" => $total
]);
