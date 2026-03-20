<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    $rating = $_POST['rating'] ?? 0;
    
    if ($service_id > 0 && $rating >= 1 && $rating <= 5) {
        $pdo = getDB();
        
        try {
            // Értékelés mentése vagy frissítése
            $stmt = $pdo->prepare("
                INSERT INTO ratings (service_id, user_id, rating) 
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE rating = ?
            ");
            $stmt->execute([$service_id, get_current_user_id(), $rating, $rating]);
            
            // Átlagos értékelés újraszámolása
            $stmt = $pdo->prepare("
                UPDATE services 
                SET average_rating = (
                    SELECT AVG(rating) FROM ratings WHERE service_id = ?
                ),
                rating_count = (
                    SELECT COUNT(*) FROM ratings WHERE service_id = ?
                )
                WHERE id = ?
            ");
            $stmt->execute([$service_id, $service_id, $service_id]);
            
            header('Location: service_view.php?id=' . $service_id . '&success=rated');
            exit;
        } catch (PDOException $e) {
            header('Location: service_view.php?id=' . $service_id . '&error=rating_failed');
            exit;
        }
    }
}

header('Location: services.php');
exit;
