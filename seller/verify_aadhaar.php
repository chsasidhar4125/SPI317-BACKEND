<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (
    empty($_POST['seller_id']) ||
    empty($_POST['aadhaar_number']) ||
    empty($_POST['aadhaar_name']) ||
    !isset($_FILES['aadhaar_doc'])
) {
    echo json_encode(["success"=>false,"message"=>"All Aadhaar fields required"]);
    exit;
}

$seller_id = (int)$_POST['seller_id'];
$aadhaar_number = $_POST['aadhaar_number'];
$aadhaar_name = trim($_POST['aadhaar_name']);

if (!preg_match("/^[0-9]{12}$/", $aadhaar_number)) {
    echo json_encode(["success"=>false,"message"=>"Invalid Aadhaar number"]);
    exit;
}

$ext = strtolower(pathinfo($_FILES['aadhaar_doc']['name'], PATHINFO_EXTENSION));
if (!in_array($ext, ['jpg','jpeg','png','pdf'])) {
    echo json_encode(["success"=>false,"message"=>"Invalid Aadhaar file"]);
    exit;
}

$dir = __DIR__ . "/../uploads/aadhaar/";
if (!is_dir($dir)) mkdir($dir, 0777, true);

$filePath = "uploads/aadhaar/" . time() . "_" . uniqid() . "." . $ext;
move_uploaded_file($_FILES['aadhaar_doc']['tmp_name'], "../".$filePath);

$stmt = $conn->prepare(
    "INSERT INTO seller_identity_verification
     (seller_id,aadhaar_number,aadhaar_name,aadhaar_doc,aadhaar_verified)
     VALUES (?,?,?,?,1)"
);
$stmt->bind_param("isss",$seller_id,$aadhaar_number,$aadhaar_name,$filePath);
$stmt->execute();

echo json_encode(["success"=>true,"message"=>"Aadhaar verified"]);
