<?php
header("Content-Type: application/json");

/* ✅ CORRECT DB INCLUDE */
require_once __DIR__ . "/../db.php";

/* -----------------------------
   READ JSON INPUT
------------------------------*/
$data = json_decode(file_get_contents("php://input"), true);

if (
    empty($data['email']) ||
    empty($data['otp'])
) {
    echo json_encode([
        "success" => false,
        "message" => "Email & OTP required"
    ]);
    exit;
}

$email = trim($data['email']);
$otp   = trim($data['otp']);

/* -----------------------------
   FETCH OTP DATA
------------------------------*/
$stmt = $conn->prepare(
    "SELECT otp, otp_expiry FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "User not found"
    ]);
    exit;
}

$row = $result->fetch_assoc();

/* -----------------------------
   OTP VALIDATION
------------------------------*/
if ($row['otp'] !== $otp) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid OTP"
    ]);
    exit;
}

if (strtotime($row['otp_expiry']) < time()) {
    echo json_encode([
        "success" => false,
        "message" => "OTP expired"
    ]);
    exit;
}

/* -----------------------------
   MARK USER VERIFIED
------------------------------*/
$update = $conn->prepare(
    "UPDATE users 
     SET is_verified = 1,
         otp = NULL,
         otp_expiry = NULL
     WHERE email = ?"
);
$update->bind_param("s", $email);
$update->execute();

/* -----------------------------
   RESPONSE
------------------------------*/
echo json_encode([
    "success" => true,
    "message" => "Email verified successfully"
]);
