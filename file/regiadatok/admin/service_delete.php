<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    
    if ($service_id > 0) {
        $pdo = getDB();
        
        // Kép lekérdezése törlés előtt
        $stmt = $pdo->prepare("SELECT image FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        $service = $stmt->fetch();
        
        // Szerviz törlése (CASCADE miatt a kommentek is törlődnek)
        $stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
        $stmt->execute([$service_id]);
        
        // Kép fájl törlése ha létezik
        if ($service && $service['image']) {
            $image_path = '../uploads/' . $service['image'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }
        
        header('Location: services.php?success=deleted');
        exit;
    }
}

header('Location: services.php');
exit;
