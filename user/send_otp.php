<?php
header("Content-Type: application/json");

/* ✅ CORRECT DB INCLUDE */
require_once __DIR__ . "/../db.php";

/* ✅ CORRECT PHPMailer INCLUDES */
require_once __DIR__ . "/../PHPMailer-master/src/Exception.php";
require_once __DIR__ . "/../PHPMailer-master/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer-master/src/SMTP.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/* -----------------------------
   READ JSON INPUT
------------------------------*/
$data = json_decode(file_get_contents("php://input"), true);

/* -----------------------------
   VALIDATION
------------------------------*/
if (!isset($data['email']) || empty(trim($data['email']))) {
    echo json_encode([
        "success" => false,
        "message" => "Email is required"
    ]);
    exit;
}

$email = trim($data['email']);

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

/* -----------------------------
   GENERATE OTP
------------------------------*/
$otp = random_int(100000, 999999);
$expiry = date("Y-m-d H:i:s", strtotime("+5 minutes"));

/* -----------------------------
   SAVE OTP
------------------------------*/
$stmt = $conn->prepare(
    "UPDATE users SET otp = ?, otp_expiry = ? WHERE email = ?"
);
$stmt->bind_param("sss", $otp, $expiry, $email);
$stmt->execute();

if ($stmt->affected_rows === 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email not registered"
    ]);
    exit;
}

/* -----------------------------
   SEND EMAIL
------------------------------*/
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;

    // 🔐 YOUR GMAIL DETAILS
    $mail->Username   = 'chilakasasi8@gmail.com';
    $mail->Password   = 'nnvqgqrjqrivvgig'; // App Password (16 chars)

    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('chilakasasi8@gmail.com', 'Seafood App');
    $mail->addAddress($email);

    $mail->isHTML(true);
    $mail->Subject = 'Your OTP Verification Code';
    $mail->Body = "
        <div style='font-family: Arial;'>
            <h2>Seafood App</h2>
            <p>Your OTP is:</p>
            <h1>$otp</h1>
            <p>This OTP is valid for <b>5 minutes</b>.</p>
        </div>
    ";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "OTP sent to email"
    ]);

} catch (Exception $e) {
    echo json_encode([
        "success" => false,
        "message" => "Email failed: " . $mail->ErrorInfo
    ]);
}
