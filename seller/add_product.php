<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (
    empty($_POST['seller_id']) ||
    empty($_POST['product_name']) ||
    empty($_POST['quantity']) ||
    empty($_POST['price'])
) {
    echo json_encode(["success"=>false,"message"=>"All fields required"]);
    exit;
}

$seller_id = (int)$_POST['seller_id'];
$name = trim($_POST['product_name']);
$qty = trim($_POST['quantity']);
$price = (float)$_POST['price'];

// Optional image
$imagePath = null;
if (!empty($_FILES['image'])) {
    $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
    if (!in_array($ext,['jpg','jpeg','png'])) {
        echo json_encode(["success"=>false,"message"=>"Invalid image"]);
        exit;
    }

    $dir = __DIR__ . "/../uploads/products/";
    if (!is_dir($dir)) mkdir($dir,0777,true);

    $imagePath = "uploads/products/" . time()."_".uniqid().".".$ext;
    move_uploaded_file($_FILES['image']['tmp_name'], "../".$imagePath);
}

$stmt = $conn->prepare(
    "INSERT INTO seller_products (seller_id, product_name, quantity, price, image)
     VALUES (?,?,?,?,?)"
);
$stmt->bind_param("issds",$seller_id,$name,$qty,$price,$imagePath);
$stmt->execute();

echo json_encode([
    "success"=>true,
    "message"=>"Product added successfully"
]);
