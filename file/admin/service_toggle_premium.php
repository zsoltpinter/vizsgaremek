<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    
    if ($service_id > 0) {
        $pdo = getDB();
        
        // Jelenlegi prémium státusz lekérdezése
        $stmt = $pdo->prepare("SELECT is_premium FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        if ($service) {
            // Prémium státusz váltása
            $new_premium = $service['is_premium'] == 1 ? 0 : 1;
            $stmt = $pdo->prepare("UPDATE services SET is_premium = ? WHERE id = ?");
            $stmt->execute([$new_premium, $service_id]);
        }
        
        header('Location: services.php?success=premium_updated');
        exit;
    }
}

header('Location: services.php');
exit;
