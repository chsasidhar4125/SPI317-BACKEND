<?php
// Set the content type to JSON for all responses
header("Content-Type: application/json");

// ✅ Correct database include (FIXED)
require_once __DIR__ . "/../db.php";

// Get the raw POST data from the request
$data = json_decode(file_get_contents("php://input"), true);

/* -----------------------------
   VALIDATION
------------------------------*/
if (
    empty($data['full_name']) ||
    empty($data['email']) ||
    empty($data['phone']) ||
    empty($data['password'])
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required"
    ]);
    exit;
}

/* -----------------------------
   ASSIGN & SANITIZE
------------------------------*/
$name = trim($data['full_name']);
$email = trim($data['email']);
$phone = trim($data['phone']);
$password = $data['password'];

/* -----------------------------
   SERVER-SIDE VALIDATIONS
------------------------------*/
// Email validation
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "Invalid email format"
    ]);
    exit;
}

// Phone validation (must be 10 digits)
if (!preg_match("/^[0-9]{10}$/", $phone)) {
    echo json_encode([
        "success" => false,
        "message" => "Phone number must be 10 digits"
    ]);
    exit;
}

// Password strength
if (strlen($password) < 6) {
    echo json_encode([
        "success" => false,
        "message" => "Password must be at least 6 characters"
    ]);
    exit;
}

/* -----------------------------
   CHECK IF EMAIL ALREADY EXISTS
------------------------------*/
$stmt = $conn->prepare(
    "SELECT id FROM users WHERE email = ?"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    echo json_encode([
        "success" => false,
        "message" => "Email already registered"
    ]);
    $stmt->close();
    $conn->close();
    exit;
}
$stmt->close();

/* -----------------------------
   HASH PASSWORD
------------------------------*/
$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

/* -----------------------------
   INSERT USER (WITH DEBUG ERROR)
------------------------------*/
$stmt = $conn->prepare(
    "INSERT INTO users (full_name, email, phone, password)
     VALUES (?, ?, ?, ?)"
);
$stmt->bind_param(
    "ssss",
    $name,
    $email,
    $phone,
    $hashedPassword
);

if ($stmt->execute()) {
    echo json_encode([
        "success" => true,
        "message" => "Account created successfully"
    ]);
} else {
    echo json_encode([
        "success" => false,
        "message" => "Database Error: " . $stmt->error
    ]);
}

// Clean up
$stmt->close();
$conn->close();
