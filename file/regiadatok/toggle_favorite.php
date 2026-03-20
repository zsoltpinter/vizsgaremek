<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    $return_url = $_POST['return_url'] ?? 'services.php';
    
    if ($service_id > 0) {
        $pdo = getDB();
        
        // Ellenőrizzük hogy már kedvenc-e
        $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND service_id = ?");
        $stmt->execute([get_current_user_id(), $service_id]);
        
        if ($stmt->fetch()) {
            // Törlés a kedvencekből
            $stmt = $pdo->prepare("DELETE FROM favorites WHERE user_id = ? AND service_id = ?");
            $stmt->execute([get_current_user_id(), $service_id]);
        } else {
            // Hozzáadás a kedvencekhez
            $stmt = $pdo->prepare("INSERT INTO favorites (user_id, service_id) VALUES (?, ?)");
            $stmt->execute([get_current_user_id(), $service_id]);
        }
    }
}

header('Location: ' . ($return_url ?? 'services.php'));
exit;
