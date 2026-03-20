<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();

// Statisztikák
$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 'pending'");
$pending_services = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 'approved'");
$approved_services = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM comments");
$total_comments = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM support");
$total_support = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'");
$pending_bookings = $stmt->fetch()['total'];

// Legújabb pending szervizek
$stmt = $pdo->query("
    SELECT s.*, u.name as user_name 
    FROM services s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.status = 'pending' 
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$recent_pending = $stmt->fetchAll();

// Support üzenetek (legújabb user üzenetek)
$stmt = $pdo->query("
    SELECT s.*, u.name as user_name 
    FROM support s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.from_admin = 0 
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$recent_support = $stmt->fetchAll();

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <h1 class="mb-4">
        <i class="bi bi-speedometer2"></i> Admin Dashboard
    </h1>
    
    <!-- Statisztikák -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-box warning">
                <i class="bi bi-clock-history"></i>
                <h3><?php echo $pending_services; ?></h3>
                <p>Pending Szervizek</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box success">
                <i class="bi bi-check-circle"></i>
                <h3><?php echo $approved_services; ?></h3>
                <p>Jóváhagyott Szervizek</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box info">
                <i class="bi bi-chat-dots"></i>
                <h3><?php echo $total_comments; ?></h3>
                <p>Összes Komment</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box primary">
                <i class="bi bi-people"></i>
                <h3><?php echo $total_users; ?></h3>
                <p>Felhasználók</p>
            </div>
        </div>
    </div>
    
    <!-- Második sor statisztikák -->
    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="stat-box warning">
                <i class="bi bi-calendar-check"></i>
                <h3><?php echo $pending_bookings; ?></h3>
                <p>Függő Foglalások</p>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="stat-box info">
                <i class="bi bi-chat-left-dots"></i>
                <h3><?php echo $total_support; ?></h3>
                <p>Support Üzenetek</p>
            </div>
        </div>
    </div>
    
    <!-- Gyors linkek -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-lightning-fill"></i> Gyors Műveletek
                </div>
                <div class="card-body">
                    <div class="d-flex flex-wrap gap-2">
                        <a href="services.php" class="btn btn-primary">
                            <i class="bi bi-list-check"></i> Szervizek Kezelése
                        </a>
                        <a href="bookings.php" class="btn btn-success">
                            <i class="bi bi-calendar-check"></i> Foglalások Kezelése
                        </a>
                        <a href="comments.php" class="btn btn-info">
                            <i class="bi bi-chat-left-text"></i> Kommentek
                        </a>
                        <a href="users.php" class="btn btn-secondary">
                            <i class="bi bi-people"></i> Felhasználók
                        </a>
                        <a href="support.php" class="btn btn-success">
                            <i class="bi bi-chat-dots"></i> Support
                        </a>
                        <a href="../index.php" class="btn btn-outline-secondary" target="_blank">
                            <i class="bi bi-box-arrow-up-right"></i> Főoldal
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <div class="row">
        <!-- Pending szervizek -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-clock-history"></i> Legújabb Pending Szervizek
                </div>
                <div class="card-body">
                    <?php if (empty($recent_pending)): ?>
                        <p class="text-muted text-center py-3">Nincsenek pending szervizek</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_pending as $service): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?php echo htmlspecialchars($service['name']); ?></h6>
                                            <p class="mb-1 small text-muted">
                                                <?php echo htmlspecialchars($service['city']); ?> - 
                                                <?php echo htmlspecialchars($service['user_name']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('Y.m.d H:i', strtotime($service['created_at'])); ?>
                                            </small>
                                        </div>
                                        <a href="services.php" class="btn btn-sm btn-warning">Kezelés</a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Support üzenetek -->
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <i class="bi bi-chat-dots"></i> Legújabb Support Üzenetek
                </div>
                <div class="card-body">
                    <?php if (empty($recent_support)): ?>
                        <p class="text-muted text-center py-3">Nincsenek új support üzenetek</p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($recent_support as $msg): ?>
                                <a href="support.php?user_id=<?php echo $msg['user_id']; ?>" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-person"></i>
                                                <?php echo htmlspecialchars($msg['user_name']); ?>
                                            </h6>
                                            <p class="mb-1 small">
                                                <?php echo htmlspecialchars(substr($msg['message'], 0, 100)); ?>
                                                <?php echo strlen($msg['message']) > 100 ? '...' : ''; ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('Y.m.d H:i', strtotime($msg['created_at'])); ?>
                                            </small>
                                        </div>
                                        <i class="bi bi-chevron-right"></i>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'footer.php'; ?>
