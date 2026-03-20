<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$pdo = getDB();

// Statisztikák lekérdezése
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
$total_users = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM services");
$total_services = $stmt->fetch()['total'];

$stmt = $pdo->query("SELECT COUNT(*) as total FROM services WHERE status = 'approved'");
$approved_services = $stmt->fetch()['total'];

// Utolsó 5 új szerviz
$stmt = $pdo->query("
    SELECT s.*, u.name as user_name 
    FROM services s 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC 
    LIMIT 5
");
$latest_services = $stmt->fetchAll();

include 'includes/header.php';
?>

<!-- Hero Section -->
<div class="hero-section">
    <div class="container text-center">
        <h1>
            <i class="bi bi-car-front-fill"></i>
            Találd meg a legjobb <span class="accent">autószervizt</span>
        </h1>
        <p class="lead">Magyarország legnagyobb autószerviz kereső platformja</p>
        <div class="mt-4">
            <a href="services.php" class="btn btn-primary btn-lg me-2">
                <i class="bi bi-search"></i> Szervizek Keresése
            </a>
            <?php if (is_logged_in()): ?>
                <a href="service_add.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-plus-circle"></i> Szerviz Hozzáadása
                </a>
            <?php else: ?>
                <a href="register.php" class="btn btn-outline-light btn-lg">
                    <i class="bi bi-person-plus"></i> Regisztráció
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="container mb-5">
    <!-- Statisztikák -->
    <div class="row mb-5">
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <i class="bi bi-people-fill"></i>
                <h3><?php echo $total_users; ?></h3>
                <p>Regisztrált Felhasználó</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <i class="bi bi-shop"></i>
                <h3><?php echo $total_services; ?></h3>
                <p>Összes Szerviz</p>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="stat-card">
                <i class="bi bi-check-circle-fill"></i>
                <h3><?php echo $approved_services; ?></h3>
                <p>Jóváhagyott Szerviz</p>
            </div>
        </div>
    </div>
    
    <!-- Legújabb szervizek -->
    <h2 class="mb-4">
        <i class="bi bi-clock-history"></i> Legújabb Szervizek
    </h2>
    
    <?php if (empty($latest_services)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Még nincsenek szervizek az adatbázisban.
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($latest_services as $service): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100">
                        <?php if ($service['image']): ?>
                            <img src="uploads/<?php echo htmlspecialchars($service['image']); ?>" 
                                 class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>">
                        <?php else: ?>
                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                 style="height: 200px;">
                                <i class="bi bi-image" style="font-size: 3rem; color: #fff;"></i>
                            </div>
                        <?php endif; ?>
                        
                        <div class="card-body">
                            <h5 class="service-card-title">
                                <?php echo htmlspecialchars($service['name']); ?>
                            </h5>
                            <p class="service-card-text">
                                <i class="bi bi-geo-alt"></i> 
                                <?php echo htmlspecialchars($service['city']); ?>, 
                                <?php echo htmlspecialchars($service['address']); ?>
                            </p>
                            <p class="service-card-text">
                                <i class="bi bi-telephone"></i> 
                                <?php echo htmlspecialchars($service['phone']); ?>
                            </p>
                            <div class="mb-2">
                                <?php if ($service['status'] === 'approved'): ?>
                                    <span class="badge badge-approved">
                                        <i class="bi bi-check-circle"></i> Jóváhagyva
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-pending">
                                        <i class="bi bi-clock"></i> Jóváhagyásra vár
                                    </span>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($service['status'] === 'approved'): ?>
                                <a href="service_view.php?id=<?php echo $service['id']; ?>" 
                                   class="btn btn-primary btn-sm w-100">
                                    <i class="bi bi-eye"></i> Részletek
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="card-footer text-muted small">
                            Feltöltve: <?php echo date('Y.m.d H:i', strtotime($service['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <!-- Miért válassz minket -->
    <div class="row mt-5">
        <div class="col-md-12">
            <h2 class="text-center mb-4">Miért az AlapSzerviz.hu?</h2>
        </div>
        <div class="col-md-4 text-center mb-4">
            <i class="bi bi-search" style="font-size: 3rem; color: var(--accent-yellow);"></i>
            <h4 class="mt-3">Könnyű Keresés</h4>
            <p class="text-muted">Találd meg gyorsan a számodra legmegfelelőbb autószervizt városod szerint.</p>
        </div>
        <div class="col-md-4 text-center mb-4">
            <i class="bi bi-star-fill" style="font-size: 3rem; color: var(--accent-yellow);"></i>
            <h4 class="mt-3">Valódi Értékelések</h4>
            <p class="text-muted">Olvasd el mások tapasztalatait és ossz meg te is véleményt.</p>
        </div>
        <div class="col-md-4 text-center mb-4">
            <i class="bi bi-shield-check" style="font-size: 3rem; color: var(--accent-yellow);"></i>
            <h4 class="mt-3">Ellenőrzött Szervizek</h4>
            <p class="text-muted">Minden szerviz adminisztrátori jóváhagyáson megy keresztül.</p>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
