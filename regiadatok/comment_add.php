<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    $comment = trim($_POST['comment'] ?? '');
    
    if (empty($comment)) {
        header('Location: service_view.php?id=' . $service_id . '&error=empty');
        exit;
    }
    
    $pdo = getDB();
    
    // Ellenőrizzük hogy létezik-e a szerviz
    $stmt = $pdo->prepare("SELECT id FROM services WHERE id = ?");
    $stmt->execute([$service_id]);
    
    if (!$stmt->fetch()) {
        header('Location: services.php');
        exit;
    }
    
    // Komment mentése
    $stmt = $pdo->prepare("INSERT INTO comments (service_id, user_id, comment) VALUES (?, ?, ?)");
    $stmt->execute([$service_id, get_current_user_id(), $comment]);
    
    header('Location: service_view.php?id=' . $service_id . '&success=comment');
    exit;
} else {
    header('Location: services.php');
    exit;
}
