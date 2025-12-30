<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

$user_id = $_GET['user_id'] ?? 1;

$res = $conn->query(
    "SELECT * FROM user_addresses WHERE user_id=$user_id"
);

$data = [];
while ($row = $res->fetch_assoc()) {
    $data[] = $row;
}

echo json_encode(["success"=>true,"addresses"=>$data]);
