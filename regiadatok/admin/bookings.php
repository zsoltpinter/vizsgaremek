<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/auth.php';

require_admin();

$pdo = getDB();

// Foglalások lekérdezése
$stmt = $pdo->query("
    SELECT b.*, 
           s.name as service_name, 
           u.name as user_name,
           u.email as user_email
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    JOIN users u ON b.user_id = u.id
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$bookings = $stmt->fetchAll();

// Státusz frissítés
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $booking_id]);
    
    header('Location: bookings.php');
    exit;
}

include 'header.php';
?>

<div class="container-fluid mt-4">
    <div class="row mb-4">
        <div class="col-12">
            <h2><i class="bi bi-calendar-check"></i> Időpont Foglalások Kezelése</h2>
        </div>
    </div>
    
    <!-- Statisztikák -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-box warning">
                <i class="bi bi-clock-history"></i>
                <h3><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'pending')); ?></h3>
                <p>Függőben</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box success">
                <i class="bi bi-check-circle"></i>
                <h3><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'confirmed')); ?></h3>
                <p>Visszaigazolva</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box info">
                <i class="bi bi-check-all"></i>
                <h3><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'completed')); ?></h3>
                <p>Teljesítve</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-box" style="border-color: var(--danger);">
                <i class="bi bi-x-circle" style="color: var(--danger);"></i>
                <h3><?php echo count(array_filter($bookings, fn($b) => $b['status'] === 'cancelled')); ?></h3>
                <p>Lemondva</p>
            </div>
        </div>
    </div>
    
    <!-- Foglalások táblázat -->
    <div class="card">
        <div class="card-header bg-primary">
            <h5 class="mb-0">
                <i class="bi bi-list"></i> Összes Foglalás
                <span class="badge bg-light text-dark"><?php echo count($bookings); ?></span>
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($bookings)): ?>
                <p class="text-center text-muted py-4">Még nincs foglalás.</p>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0" style="font-size: 0.9rem;">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">ID</th>
                                <th style="width: 120px;">Dátum & Idő</th>
                                <th style="width: 150px;">Ügyfél</th>
                                <th style="width: 150px;">Kapcsolat</th>
                                <th style="width: 150px;">Szerviz</th>
                                <th style="width: 200px;">Szolgáltatások</th>
                                <th style="width: 100px;">Időtartam</th>
                                <th style="width: 120px;">Státusz</th>
                                <th style="width: 100px;">Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td>
                                        <strong><?php echo date('Y.m.d', strtotime($booking['booking_date'])); ?></strong>
                                        <br>
                                        <small><?php echo date('H:i', strtotime($booking['booking_time'])); ?></small>
                                    </td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['user_name']); ?></small>
                                    </td>
                                    <td>
                                        <small>
                                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($booking['phone']); ?>
                                            <br>
                                            <i class="bi bi-envelope"></i> <?php echo htmlspecialchars($booking['email']); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($booking['service_name']); ?>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($booking['services_requested']) {
                                            $services = json_decode($booking['services_requested'], true);
                                            if ($services && is_array($services)) {
                                                echo '<small>';
                                                foreach ($services as $svc) {
                                                    echo '<i class="bi bi-check-circle text-success"></i> ' . htmlspecialchars($svc) . '<br>';
                                                }
                                                echo '</small>';
                                            } else {
                                                echo '<small class="text-muted">-</small>';
                                            }
                                        } else {
                                            echo '<small class="text-muted">-</small>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php if ($booking['estimated_duration']): ?>
                                            <span class="badge bg-info">
                                                <i class="bi bi-clock"></i> 
                                                <?php 
                                                $hours = floor($booking['estimated_duration'] / 60);
                                                $mins = $booking['estimated_duration'] % 60;
                                                if ($hours > 0) {
                                                    echo $hours . 'ó ' . ($mins > 0 ? $mins . 'p' : '');
                                                } else {
                                                    echo $mins . ' perc';
                                                }
                                                ?>
                                            </span>
                                        <?php else: ?>
                                            <small class="text-muted">-</small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php
                                        $status_badges = [
                                            'pending' => '<span class="badge bg-warning"><i class="bi bi-clock"></i> Függőben</span>',
                                            'confirmed' => '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Visszaigazolva</span>',
                                            'cancelled' => '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Lemondva</span>',
                                            'completed' => '<span class="badge bg-info"><i class="bi bi-check-all"></i> Teljesítve</span>'
                                        ];
                                        echo $status_badges[$booking['status']] ?? '';
                                        ?>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" name="update_status" class="btn btn-success btn-sm" title="Visszaigazolás">
                                                        <i class="bi bi-check"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($booking['status'] === 'confirmed'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" name="update_status" class="btn btn-info btn-sm" title="Teljesítve">
                                                        <i class="bi bi-check-all"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if ($booking['status'] !== 'cancelled' && $booking['status'] !== 'completed'): ?>
                                                <form method="POST" action="" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" name="update_status" class="btn btn-danger btn-sm" title="Lemondás">
                                                        <i class="bi bi-x"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
