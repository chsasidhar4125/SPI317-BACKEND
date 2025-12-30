<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (empty($_POST['order_id'])) {
    echo json_encode(["success"=>false,"message"=>"Order ID required"]);
    exit;
}

$order_id = (int)$_POST['order_id'];

$conn->query(
    "UPDATE orders
     SET status='Ready'
     WHERE id=$order_id AND status IN ('Confirmed','Preparing')"
);

echo json_encode([
    "success" => true,
    "message" => "Order marked as ready"
]);
