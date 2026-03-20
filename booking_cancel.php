<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/booking_helper.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bookings.php');
    exit;
}

$booking_id = $_POST['booking_id'] ?? 0;

$result = cancel_booking($booking_id, get_current_user_id());

if ($result['success']) {
    $_SESSION['booking_success'] = 'Foglalás sikeresen lemondva!';
} else {
    $_SESSION['booking_error'] = $result['error'];
}

header('Location: bookings.php');
exit;
