<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../db.php";

if (empty($_POST['seller_id'])) {
    echo json_encode(["success"=>false,"message"=>"Seller ID required"]);
    exit;
}

$seller_id = (int)$_POST['seller_id'];

$check = $conn->query(
    "SELECT * FROM seller_identity_verification WHERE seller_id=$seller_id"
)->fetch_assoc();

if (
    !$check['aadhaar_verified'] ||
    !$check['liveness_verified'] ||
    !$check['face_match_verified'] ||
    $check['police_verification_status'] !== 'verified'
) {
    echo json_encode(["success"=>false,"message"=>"All steps not completed"]);
    exit;
}

$conn->query(
    "UPDATE seller_identity_verification
     SET verification_status='submitted'
     WHERE seller_id=$seller_id"
);

$conn->query(
    "UPDATE sellers SET status='submitted' WHERE id=$seller_id"
);

echo json_encode([
    "success"=>true,
    "message"=>"Identity verification submitted"
]);
