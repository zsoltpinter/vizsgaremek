<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bookings.php');
    exit;
}

$booking_id = intval($_POST['booking_id'] ?? 0);
$status = $_POST['status'] ?? '';

// Validáció
$valid_statuses = ['pending', 'confirmed', 'cancelled', 'completed'];
if (!in_array($status, $valid_statuses) || $booking_id <= 0) {
    $_SESSION['error'] = 'Érvénytelen státusz vagy foglalás azonosító!';
    header('Location: bookings.php');
    exit;
}

$pdo = getDB();

// Foglalás ellenőrzése
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Nem található ilyen foglalás!';
    header('Location: bookings.php');
    exit;
}

// Státusz frissítése
try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$status, $booking_id]);
    
    $status_names = [
        'pending' => 'Függőben',
        'confirmed' => 'Megerősítve',
        'cancelled' => 'Lemondva',
        'completed' => 'Befejezve'
    ];
    
    $_SESSION['success'] = "Foglalás státusza sikeresen frissítve: {$status_names[$status]}";
} catch (Exception $e) {
    $_SESSION['error'] = 'Hiba történt a státusz frissítése során!';
}

header('Location: bookings.php');
exit;
