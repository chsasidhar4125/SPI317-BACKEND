<?php
header("Content-Type: application/json");
require_once "../db.php";

$order_id = $_GET['order_id'] ?? null;

if (!$order_id) {
    echo json_encode([
        "success" => false,
        "message" => "Order ID required"
    ]);
    exit;
}

/* ---------------------------
   ORDER + DELIVERY PARTNER
----------------------------*/
$order_sql = "
    SELECT 
        o.order_code,
        o.status,
        o.current_lat,
        o.current_lng,
        o.customer_lat,
        o.customer_lng,
        o.is_live,
        dp.name AS partner_name,
        dp.phone,
        dp.rating
    FROM orders o
    LEFT JOIN delivery_partners dp 
        ON o.delivery_partner_id = dp.id
    WHERE o.id = ?
";

$stmt = $conn->prepare($order_sql);
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    echo json_encode([
        "success" => false,
        "message" => "Order not found"
    ]);
    exit;
}

/* ---------------------------
   ORDER TIMELINE
----------------------------*/
$timeline_sql = "
    SELECT status, message, created_at
    FROM order_tracking
    WHERE order_id = ?
    ORDER BY created_at ASC
";

$tstmt = $conn->prepare($timeline_sql);
$tstmt->bind_param("i", $order_id);
$tstmt->execute();

$timeline = [];
$res = $tstmt->get_result();
while ($row = $res->fetch_assoc()) {
    $timeline[] = $row;
}

/* ---------------------------
   RESPONSE
----------------------------*/
echo json_encode([
    "success" => true,
    "order_code" => $order['order_code'],
    "is_live" => (bool)$order['is_live'],
    "map" => [
        "delivery_lat" => $order['current_lat'],
        "delivery_lng" => $order['current_lng'],
        "customer_lat" => $order['customer_lat'],
        "customer_lng" => $order['customer_lng']
    ],
    "delivery_partner" => [
        "name" => $order['partner_name'],
        "phone" => $order['phone'],
        "rating" => $order['rating']
    ],
    "timeline" => $timeline
]);
