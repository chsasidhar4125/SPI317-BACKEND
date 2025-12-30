<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

$data = json_decode(file_get_contents("php://input"), true);

$stmt = $conn->prepare(
    "INSERT INTO user_addresses
     (user_id,label,full_name,phone,address,city,pincode,is_default)
     VALUES (?,?,?,?,?,?,?,?)"
);

$stmt->bind_param(
    "issssssi",
    $data['user_id'],
    $data['label'],
    $data['full_name'],
    $data['phone'],
    $data['address'],
    $data['city'],
    $data['pincode'],
    $data['is_default']
);

$stmt->execute();

echo json_encode(["success"=>true,"message"=>"Address added"]);
