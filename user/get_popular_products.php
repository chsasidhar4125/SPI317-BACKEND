<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

$res = $conn->query(
    "SELECT name, price_per_kg, rating, freshness, image
     FROM products
     WHERE is_active=1
     ORDER BY rating DESC"
);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["success"=>true,"products"=>$data]);
