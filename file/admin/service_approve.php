<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $service_id = $_POST['service_id'] ?? 0;
    
    if ($service_id > 0) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE services SET status = 'approved' WHERE id = ?");
        $stmt->execute([$service_id]);
        
        header('Location: services.php?success=approved');
        exit;
    }
}

header('Location: services.php');
exit;
