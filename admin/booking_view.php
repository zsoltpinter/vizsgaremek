<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$booking_id = intval($_GET['id'] ?? 0);

if ($booking_id <= 0) {
    $_SESSION['error'] = 'Érvénytelen foglalás azonosító!';
    header('Location: bookings.php');
    exit;
}

$pdo = getDB();

// Foglalás lekérdezése
$stmt = $pdo->prepare("
    SELECT b.*, 
           u.name as user_name, u.email as user_email,
           s.name as service_name, s.city, s.address, s.phone as service_phone
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
    WHERE b.id = ?
");
$stmt->execute([$booking_id]);
$booking = $stmt->fetch();

if (!$booking) {
    $_SESSION['error'] = 'Nem található ilyen foglalás!';
    header('Location: bookings.php');
    exit;
}

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-calendar-check"></i> Foglalás Részletei
        </h1>
        <a href="bookings.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Vissza
        </a>
    </div>
    
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-person"></i> Ügyfél Adatai</h5>
                </div>
                <div class="card-body">
                    <p><strong>Név:</strong> <?php echo htmlspecialchars($booking['name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($booking['email']); ?></p>
                    <p><strong>Telefon:</strong> <?php echo htmlspecialchars($booking['phone']); ?></p>
                    <p><strong>Felhasználó:</strong> <?php echo htmlspecialchars($booking['user_name']); ?></p>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="bi bi-shop"></i> Szerviz Adatai</h5>
                </div>
                <div class="card-body">
                    <p><strong>Név:</strong> <?php echo htmlspecialchars($booking['service_name']); ?></p>
                    <p><strong>Város:</strong> <?php echo htmlspecialchars($booking['city']); ?></p>
                    <p><strong>Cím:</strong> <?php echo htmlspecialchars($booking['address']); ?></p>
                    <p><strong>Telefon:</strong> <?php echo htmlspecialchars($booking['service_phone']); ?></p>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="bi bi-calendar3"></i> Foglalás Részletei</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Dátum:</strong> <?php echo date('Y. m. d.', strtotime($booking['booking_date'])); ?></p>
                    <p><strong>Időpont:</strong> <?php echo date('H:i', strtotime($booking['booking_time'])); ?></p>
                    <p><strong>Becsült időtartam:</strong> <?php echo $booking['estimated_duration']; ?> perc</p>
                </div>
                <div class="col-md-6">
                    <p><strong>Státusz:</strong> 
                        <?php if ($booking['status'] === 'pending'): ?>
                            <span class="badge bg-warning">
                                <i class="bi bi-clock"></i> Függőben
                            </span>
                        <?php elseif ($booking['status'] === 'confirmed'): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Megerősítve
                            </span>
                        <?php elseif ($booking['status'] === 'cancelled'): ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Lemondva
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-check-circle-fill"></i> Befejezve
                            </span>
                        <?php endif; ?>
                    </p>
                    <p><strong>Létrehozva:</strong> <?php echo date('Y. m. d. H:i', strtotime($booking['created_at'])); ?></p>
                </div>
            </div>
            
            <hr>
            
            <p><strong>Kért szolgáltatások:</strong></p>
            <p class="text-muted"><?php echo nl2br(htmlspecialchars($booking['services_requested'])); ?></p>
            
            <?php if ($booking['message']): ?>
                <hr>
                <p><strong>Megjegyzés:</strong></p>
                <p class="text-muted"><?php echo nl2br(htmlspecialchars($booking['message'])); ?></p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header bg-warning">
            <h5 class="mb-0"><i class="bi bi-gear"></i> Műveletek</h5>
        </div>
        <div class="card-body">
            <div class="btn-group" role="group">
                <?php if ($booking['status'] === 'pending'): ?>
                    <form method="POST" action="booking_update_status.php" style="display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="status" value="confirmed">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-check-circle"></i> Megerősítés
                        </button>
                    </form>
                <?php endif; ?>
                
                <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                    <form method="POST" action="booking_update_status.php" style="display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="btn btn-secondary">
                            <i class="bi bi-check-circle-fill"></i> Befejezés
                        </button>
                    </form>
                    
                    <form method="POST" action="booking_update_status.php" style="display: inline;">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="btn btn-warning" 
                                onclick="return confirm('Biztosan lemondod ezt a foglalást?')">
                            <i class="bi bi-x-circle"></i> Lemondás
                        </button>
                    </form>
                <?php endif; ?>
                
                <form method="POST" action="booking_delete.php" style="display: inline;">
                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                    <button type="submit" class="btn btn-danger" 
                            onclick="return confirm('Biztosan törölni szeretnéd ezt a foglalást? Ez a művelet nem visszavonható!')">
                        <i class="bi bi-trash"></i> Törlés
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
