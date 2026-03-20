<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/booking_helper.php';

require_login();

$bookings = get_user_bookings(get_current_user_id());

// Csoportosítás státusz szerint
$active_bookings = array_filter($bookings, fn($b) => in_array($b['status'], ['pending', 'confirmed']));
$cancelled_bookings = array_filter($bookings, fn($b) => $b['status'] === 'cancelled');
$completed_bookings = array_filter($bookings, fn($b) => $b['status'] === 'completed');

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <h1 class="mb-4">
        <i class="bi bi-calendar-check"></i> Foglalásaim
    </h1>
    
    <?php if (isset($_SESSION['booking_success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['booking_success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['booking_success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['booking_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['booking_error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['booking_error']); ?>
    <?php endif; ?>
    
    <?php if (empty($bookings)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Még nincsenek foglalásaid. 
            <a href="services.php">Böngéssz a szervizek között</a> és foglalj időpontot!
        </div>
    <?php else: ?>
        
        <!-- Aktív foglalások -->
        <?php if (!empty($active_bookings)): ?>
            <h3 class="mb-3">
                <i class="bi bi-circle-fill text-success"></i> Aktív Foglalások
            </h3>
            <div class="row mb-4">
                <?php foreach ($active_bookings as $booking): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-shop"></i> <?php echo htmlspecialchars($booking['service_name']); ?>
                                </h5>
                                <p class="card-text">
                                    <strong><i class="bi bi-calendar3"></i> Időpont:</strong> 
                                    <?php echo date('Y.m.d', strtotime($booking['booking_date'])); ?> 
                                    <?php echo date('H:i', strtotime($booking['booking_time'])); ?>
                                    <br>
                                    <strong><i class="bi bi-clock"></i> Időtartam:</strong> 
                                    <?php echo $booking['estimated_duration']; ?> perc
                                    <br>
                                    <strong><i class="bi bi-tools"></i> Szolgáltatások:</strong> 
                                    <?php echo htmlspecialchars($booking['services_requested'] ?? $booking['service_type'] ?? 'N/A'); ?>
                                    <br>
                                    <strong><i class="bi bi-geo-alt"></i> Helyszín:</strong> 
                                    <?php echo htmlspecialchars($booking['city']); ?>, <?php echo htmlspecialchars($booking['address']); ?>
                                    <br>
                                    <strong><i class="bi bi-telephone"></i> Telefon:</strong> 
                                    <?php echo htmlspecialchars($booking['phone']); ?>
                                    <?php if ($booking['notes']): ?>
                                        <br>
                                        <strong><i class="bi bi-chat-left-text"></i> Megjegyzés:</strong> 
                                        <?php echo htmlspecialchars($booking['notes']); ?>
                                    <?php endif; ?>
                                </p>
                                <div class="d-flex gap-2">
                                    <a href="service_view.php?id=<?php echo $booking['service_id']; ?>" 
                                       class="btn btn-sm btn-primary">
                                        <i class="bi bi-eye"></i> Szerviz Megtekintése
                                    </a>
                                    <form method="POST" action="booking_cancel.php" style="display: inline;">
                                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('Biztosan lemondod ezt a foglalást?')">
                                            <i class="bi bi-x-circle"></i> Lemondás
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="card-footer text-muted small">
                                Foglalva: <?php echo date('Y.m.d H:i', strtotime($booking['created_at'])); ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Lemondott foglalások -->
        <?php if (!empty($cancelled_bookings)): ?>
            <h3 class="mb-3">
                <i class="bi bi-x-circle text-danger"></i> Lemondott Foglalások
            </h3>
            <div class="row mb-4">
                <?php foreach ($cancelled_bookings as $booking): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-danger">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-shop"></i> <?php echo htmlspecialchars($booking['service_name']); ?>
                                </h5>
                                <p class="card-text">
                                    <strong><i class="bi bi-calendar3"></i> Időpont volt:</strong> 
                                    <?php echo date('Y.m.d H:i', strtotime($booking['booking_date'] . ' ' . $booking['booking_time'])); ?>
                                    <br>
                                    <strong><i class="bi bi-tools"></i> Szolgáltatások:</strong> 
                                    <?php echo htmlspecialchars($booking['services_requested'] ?? $booking['service_type'] ?? 'N/A'); ?>
                                    <br>
                                    <span class="badge bg-danger">Lemondva</span>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <!-- Befejezett foglalások -->
        <?php if (!empty($completed_bookings)): ?>
            <h3 class="mb-3">
                <i class="bi bi-check-circle text-secondary"></i> Befejezett Foglalások
            </h3>
            <div class="row mb-4">
                <?php foreach ($completed_bookings as $booking): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-secondary">
                            <div class="card-body">
                                <h5 class="card-title">
                                    <i class="bi bi-shop"></i> <?php echo htmlspecialchars($booking['service_name']); ?>
                                </h5>
                                <p class="card-text">
                                    <strong><i class="bi bi-calendar3"></i> Időpont volt:</strong> 
                                    <?php echo date('Y.m.d H:i', strtotime($booking['booking_date'] . ' ' . $booking['booking_time'])); ?>
                                    <br>
                                    <strong><i class="bi bi-tools"></i> Szolgáltatások:</strong> 
                                    <?php echo htmlspecialchars($booking['services_requested'] ?? $booking['service_type'] ?? 'N/A'); ?>
                                    <br>
                                    <span class="badge bg-secondary">Befejezve</span>
                                </p>
                                <a href="service_view.php?id=<?php echo $booking['service_id']; ?>" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-star"></i> Értékelés Írása
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
