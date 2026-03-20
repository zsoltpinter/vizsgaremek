<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: bookings.php');
    exit;
}

$booking_id = intval($_POST['booking_id'] ?? 0);

if ($booking_id <= 0) {
    $_SESSION['error'] = 'Érvénytelen foglalás azonosító!';
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

// Foglalás törlése
try {
    $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    $_SESSION['success'] = 'Foglalás sikeresen törölve!';
} catch (Exception $e) {
    $_SESSION['error'] = 'Hiba történt a foglalás törlése során!';
}

header('Location: bookings.php');
exit;
