<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/booking_helper.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: services.php');
    exit;
}

$service_id = $_POST['service_id'] ?? 0;
$customer_name = trim($_POST['customer_name'] ?? '');
$customer_phone = trim($_POST['customer_phone'] ?? '');
$booking_date = $_POST['booking_date'] ?? '';
$booking_time = $_POST['booking_time'] ?? '';
$service_type = trim($_POST['service_type'] ?? '');
$estimated_duration = intval($_POST['estimated_duration'] ?? 0);
$notes = trim($_POST['notes'] ?? '');

// Validáció
if (empty($service_id) || empty($customer_name) || empty($customer_phone) || empty($booking_date) || empty($booking_time) || empty($service_type) || $estimated_duration <= 0) {
    $_SESSION['booking_error'] = 'Minden kötelező mező kitöltése szükséges!';
    header("Location: booking_form.php?service_id={$service_id}");
    exit;
}

// Email lekérése a bejelentkezett userből
$pdo = getDB();
$stmt = $pdo->prepare("SELECT email FROM users WHERE id = ?");
$stmt->execute([get_current_user_id()]);
$user = $stmt->fetch();
$customer_email = $user['email'] ?? '';

// Foglalás létrehozása
$result = create_booking(get_current_user_id(), $service_id, [
    'booking_date' => $booking_date,
    'booking_time' => $booking_time,
    'customer_name' => $customer_name,
    'customer_phone' => $customer_phone,
    'customer_email' => $customer_email,
    'service_type' => $service_type,
    'estimated_duration' => $estimated_duration,
    'notes' => $notes
]);

if ($result['success']) {
    $_SESSION['booking_success'] = 'Foglalás sikeresen létrehozva!';
    header("Location: bookings.php");
} else {
    $_SESSION['booking_error'] = $result['error'];
    header("Location: booking_form.php?service_id={$service_id}");
}
exit;
