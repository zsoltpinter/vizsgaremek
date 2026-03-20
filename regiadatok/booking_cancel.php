<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Nem vagy bejelentkezve']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Hibás kérés']);
    exit;
}

$booking_id = isset($_POST['booking_id']) ? (int)$_POST['booking_id'] : 0;

if (!$booking_id) {
    echo json_encode(['success' => false, 'message' => 'Hiányzó foglalás ID']);
    exit;
}

$pdo = getDB();

// Ellenőrizzük, hogy a foglalás a bejelentkezett userhez tartozik-e
$stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE id = ? AND user_id = ?");
$stmt->execute([$booking_id, get_current_user_id()]);
$booking = $stmt->fetch();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Foglalás nem található']);
    exit;
}

if ($booking['status'] === 'cancelled') {
    echo json_encode(['success' => false, 'message' => 'Ez a foglalás már le van mondva']);
    exit;
}

if ($booking['status'] === 'completed') {
    echo json_encode(['success' => false, 'message' => 'Teljesített foglalást nem lehet lemondani']);
    exit;
}

// Foglalás lemondása
$stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");

if ($stmt->execute([$booking_id])) {
    echo json_encode(['success' => true, 'message' => 'Foglalás sikeresen lemondva']);
} else {
    echo json_encode(['success' => false, 'message' => 'Hiba történt a lemondás során']);
}
