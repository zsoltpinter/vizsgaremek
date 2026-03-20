<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/api_helper.php';

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

// Autós témájú API adatok lekérése
$automotive_data = get_automotive_data();

// Időjárási adatok vezetési körülményekkel
$weather_data = get_weather_for_drivers();

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
    
    <!-- Autós Hírek és Tippek (API) -->
    <?php if (!empty($automotive_data)): ?>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <h2 class="mb-4">
                <i class="bi bi-newspaper"></i> Autós Hírek és Tippek
            </h2>
        </div>
        
        <!-- Hírek -->
        <?php if (!empty($automotive_data['news'])): ?>
            <?php foreach ($automotive_data['news'] as $news): ?>
                <div class="col-md-6 col-lg-3 mb-3">
                    <div class="card h-100 border-warning">
                        <div class="card-body text-center">
                            <i class="<?php echo htmlspecialchars($news['icon']); ?>" 
                               style="font-size: 2.5rem; color: var(--accent-yellow);"></i>
                            <h5 class="card-title mt-3"><?php echo htmlspecialchars($news['title']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars($news['summary']); ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
        
        <!-- Karbantartási Tippek -->
        <?php if (!empty($automotive_data['tips'])): ?>
        <div class="col-md-12 mt-4">
            <div class="alert alert-warning">
                <h5><i class="bi bi-lightbulb-fill"></i> Karbantartási Tippek</h5>
                <ul class="mb-0">
                    <?php foreach ($automotive_data['tips'] as $tip): ?>
                        <li><?php echo htmlspecialchars($tip); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <!-- Időjárás és Vezetési Körülmények -->
    <?php if (!empty($weather_data)): ?>
    <div class="row mt-5 mb-5">
        <div class="col-md-12">
            <h2 class="mb-4">
                <i class="bi bi-cloud-sun-fill"></i> Időjárás és Vezetési Körülmények - Budapest
            </h2>
        </div>
        
        <div class="col-md-12">
            <div class="card border-<?php 
                echo $weather_data['driving_conditions']['overall'] === 'good' ? 'success' : 
                    ($weather_data['driving_conditions']['overall'] === 'moderate' ? 'warning' : 'danger'); 
            ?>">
                <div class="card-body">
                    <div class="row">
                        <!-- Aktuális időjárás -->
                        <div class="col-md-4 text-center border-end">
                            <i class="bi bi-thermometer-half" style="font-size: 3rem; color: var(--accent-yellow);"></i>
                            <h3 class="mt-2"><?php echo $weather_data['temperature']; ?>°C</h3>
                            <p class="text-muted mb-1"><?php echo $weather_data['description']; ?></p>
                            <p class="text-muted small">Érzékelt: <?php echo $weather_data['feels_like']; ?>°C</p>
                        </div>
                        
                        <!-- Vezetési körülmények -->
                        <div class="col-md-4 text-center border-end">
                            <i class="bi bi-<?php 
                                echo $weather_data['driving_conditions']['overall'] === 'good' ? 'check-circle-fill' : 
                                    ($weather_data['driving_conditions']['overall'] === 'moderate' ? 'exclamation-triangle-fill' : 'x-circle-fill'); 
                            ?>" style="font-size: 3rem; color: <?php 
                                echo $weather_data['driving_conditions']['overall'] === 'good' ? '#28a745' : 
                                    ($weather_data['driving_conditions']['overall'] === 'moderate' ? '#ffc107' : '#dc3545'); 
                            ?>;"></i>
                            <h5 class="mt-2"><?php echo $weather_data['driving_conditions']['overall_text']; ?></h5>
                            <?php if (!empty($weather_data['driving_conditions']['warnings'])): ?>
                                <div class="alert alert-<?php 
                                    echo $weather_data['driving_conditions']['overall'] === 'poor' ? 'danger' : 'warning'; 
                                ?> mt-2 mb-0 text-start">
                                    <strong><i class="bi bi-exclamation-triangle"></i> Figyelmeztetések:</strong>
                                    <ul class="mb-0 mt-1">
                                        <?php foreach ($weather_data['driving_conditions']['warnings'] as $warning): ?>
                                            <li><?php echo htmlspecialchars($warning); ?></li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Részletes adatok -->
                        <div class="col-md-4">
                            <h6><i class="bi bi-eye"></i> Látótávolság</h6>
                            <p class="mb-2"><?php echo $weather_data['visibility']; ?> km</p>
                            
                            <h6><i class="bi bi-wind"></i> Szél</h6>
                            <p class="mb-2"><?php echo $weather_data['wind_speed']; ?> km/h 
                                (<?php echo get_wind_direction($weather_data['wind_deg']); ?>)</p>
                            
                            <h6><i class="bi bi-droplet"></i> Páratartalom</h6>
                            <p class="mb-2"><?php echo $weather_data['humidity']; ?>%</p>
                            
                            <h6><i class="bi bi-speedometer"></i> Légnyomás</h6>
                            <p class="mb-0"><?php echo $weather_data['pressure']; ?> hPa</p>
                        </div>
                    </div>
                    
                    <!-- Vezetési tippek -->
                    <?php if (!empty($weather_data['driving_conditions']['tips'])): ?>
                    <hr>
                    <div class="row">
                        <div class="col-md-12">
                            <h6><i class="bi bi-lightbulb-fill"></i> Vezetési Tippek:</h6>
                            <ul class="mb-0">
                                <?php foreach ($weather_data['driving_conditions']['tips'] as $tip): ?>
                                    <li><?php echo htmlspecialchars($tip); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="card-footer text-muted small text-center">
                    <i class="bi bi-clock"></i> Frissítve: <?php echo date('Y.m.d H:i'); ?> | 
                    <i class="bi bi-arrow-clockwise"></i> Automatikus frissítés 30 percenként
                </div>
            </div>
        </div>
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
