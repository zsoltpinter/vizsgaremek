<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();

// Szűrés
$status_filter = $_GET['status'] ?? 'all';

// Foglalások lekérdezése
$sql = "
    SELECT b.*, 
           u.name as user_name, u.email as user_email,
           s.name as service_name, s.city, s.phone
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN services s ON b.service_id = s.id
";

if ($status_filter !== 'all') {
    $sql .= " WHERE b.status = :status";
}

$sql .= " ORDER BY b.booking_date DESC, b.booking_time DESC";

$stmt = $pdo->prepare($sql);
if ($status_filter !== 'all') {
    $stmt->bindParam(':status', $status_filter);
}
$stmt->execute();
$bookings = $stmt->fetchAll();

// Statisztikák
$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status IN ('pending', 'confirmed')");
$active_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'cancelled'");
$cancelled_count = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'completed'");
$completed_count = $stmt->fetch()['count'];

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <h1 class="mb-4">
        <i class="bi bi-calendar-check"></i> Foglalások Kezelése
    </h1>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Statisztikák -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-warning text-white">
                <div class="card-body">
                    <h5><i class="bi bi-clock"></i> Függőben</h5>
                    <h2><?php echo $active_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5><i class="bi bi-check-circle"></i> Megerősítve</h5>
                    <h2><?php 
                        $stmt = $pdo->query("SELECT COUNT(*) as count FROM bookings WHERE status = 'confirmed'");
                        echo $stmt->fetch()['count']; 
                    ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5><i class="bi bi-x-circle"></i> Lemondott</h5>
                    <h2><?php echo $cancelled_count; ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-secondary text-white">
                <div class="card-body">
                    <h5><i class="bi bi-check-circle-fill"></i> Befejezett</h5>
                    <h2><?php echo $completed_count; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Szűrés -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="btn-group" role="group">
                <a href="?status=all" class="btn btn-<?php echo $status_filter === 'all' ? 'primary' : 'outline-primary'; ?>">
                    <i class="bi bi-list"></i> Összes (<?php echo count($bookings); ?>)
                </a>
                <a href="?status=pending" class="btn btn-<?php echo $status_filter === 'pending' ? 'warning' : 'outline-warning'; ?>">
                    <i class="bi bi-clock"></i> Függőben (<?php echo $active_count; ?>)
                </a>
                <a href="?status=confirmed" class="btn btn-<?php echo $status_filter === 'confirmed' ? 'success' : 'outline-success'; ?>">
                    <i class="bi bi-check-circle"></i> Megerősítve
                </a>
                <a href="?status=cancelled" class="btn btn-<?php echo $status_filter === 'cancelled' ? 'danger' : 'outline-danger'; ?>">
                    <i class="bi bi-x-circle"></i> Lemondott (<?php echo $cancelled_count; ?>)
                </a>
                <a href="?status=completed" class="btn btn-<?php echo $status_filter === 'completed' ? 'secondary' : 'outline-secondary'; ?>">
                    <i class="bi bi-check-circle-fill"></i> Befejezett (<?php echo $completed_count; ?>)
                </a>
            </div>
        </div>
    </div>
    
    <!-- Foglalások táblázat -->
    <div class="card">
        <div class="card-body">
            <?php if (empty($bookings)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 4rem;"></i>
                    <p class="mt-3">Nincsenek foglalások ebben a kategóriában.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Név</th>
                                <th>Telefon</th>
                                <th>Szerviz</th>
                                <th>Dátum</th>
                                <th>Időpont</th>
                                <th>Időtartam</th>
                                <th>Szolgáltatások</th>
                                <th>Státusz</th>
                                <th>Létrehozva</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($bookings as $booking): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['name']); ?></strong>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($booking['email']); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($booking['phone']); ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($booking['service_name']); ?></strong>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($booking['city']); ?>
                                        </small>
                                    </td>
                                    <td><?php echo date('Y.m.d', strtotime($booking['booking_date'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($booking['booking_time'])); ?></td>
                                    <td><?php echo $booking['estimated_duration']; ?> perc</td>
                                    <td><?php echo htmlspecialchars($booking['services_requested']); ?></td>
                                    <td>
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
                                    </td>
                                    <td>
                                        <small><?php echo date('Y.m.d H:i', strtotime($booking['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group-vertical btn-group-sm" role="group">
                                            <a href="booking_view.php?id=<?php echo $booking['id']; ?>" 
                                               class="btn btn-info btn-sm mb-1" title="Részletek">
                                                <i class="bi bi-eye"></i> Részletek
                                            </a>
                                            
                                            <?php if ($booking['status'] === 'pending'): ?>
                                                <form method="POST" action="booking_update_status.php" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="confirmed">
                                                    <button type="submit" class="btn btn-success btn-sm mb-1" title="Megerősítés">
                                                        <i class="bi bi-check-circle"></i> Megerősít
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <?php if (in_array($booking['status'], ['pending', 'confirmed'])): ?>
                                                <form method="POST" action="booking_update_status.php" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="completed">
                                                    <button type="submit" class="btn btn-secondary btn-sm mb-1" title="Befejezés">
                                                        <i class="bi bi-check-circle-fill"></i> Befejez
                                                    </button>
                                                </form>
                                                
                                                <form method="POST" action="booking_update_status.php" style="display: inline;">
                                                    <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                    <input type="hidden" name="status" value="cancelled">
                                                    <button type="submit" class="btn btn-warning btn-sm mb-1" 
                                                            onclick="return confirm('Biztosan lemondod ezt a foglalást?')" title="Lemondás">
                                                        <i class="bi bi-x-circle"></i> Lemond
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" action="booking_delete.php" style="display: inline;">
                                                <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        onclick="return confirm('Biztosan törölni szeretnéd ezt a foglalást? Ez a művelet nem visszavonható!')" title="Törlés">
                                                    <i class="bi bi-trash"></i> Töröl
                                                </button>
                                            </form>
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
