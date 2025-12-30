<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (empty($_POST['seller_id']) || !isset($_FILES['selfie'])) {
    echo json_encode(["success"=>false,"message"=>"Selfie required"]);
    exit;
}

$seller_id = (int)$_POST['seller_id'];

$check = $conn->query(
    "SELECT aadhaar_verified FROM seller_identity_verification
     WHERE seller_id=$seller_id"
)->fetch_assoc();

if (!$check || !$check['aadhaar_verified']) {
    echo json_encode(["success"=>false,"message"=>"Complete Aadhaar step first"]);
    exit;
}

$ext = strtolower(pathinfo($_FILES['selfie']['name'], PATHINFO_EXTENSION));
if (!in_array($ext,['jpg','jpeg','png'])) {
    echo json_encode(["success"=>false,"message"=>"Invalid selfie format"]);
    exit;
}

$dir = __DIR__ . "/../uploads/selfie/";
if (!is_dir($dir)) mkdir($dir, 0777, true);

$filePath = "uploads/selfie/" . time() . "_" . uniqid() . "." . $ext;
move_uploaded_file($_FILES['selfie']['tmp_name'], "../".$filePath);

$conn->query(
    "UPDATE seller_identity_verification
     SET selfie_image='$filePath', liveness_verified=1
     WHERE seller_id=$seller_id"
);

echo json_encode(["success"=>true,"message"=>"Liveness verified"]);
