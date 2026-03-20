<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$pdo = getDB();

// Keresési paraméterek
$search_city = $_GET['city'] ?? '';
$search_name = $_GET['name'] ?? '';

// SQL építése szűrőkkel
$sql = "
    SELECT s.*, u.name as user_name 
    FROM services s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.status = 'approved'
";

$params = [];

if (!empty($search_city)) {
    $sql .= " AND s.city LIKE ?";
    $params[] = '%' . $search_city . '%';
}

if (!empty($search_name)) {
    $sql .= " AND s.name LIKE ?";
    $params[] = '%' . $search_name . '%';
}

$sql .= " ORDER BY s.is_premium DESC, s.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

// Városok listája a dropdown-hoz
$stmt_cities = $pdo->query("SELECT DISTINCT city FROM services WHERE status = 'approved' ORDER BY city");
$cities = $stmt_cities->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>
                <i class="bi bi-search"></i> Autószervizek
            </h1>
            <p class="lead text-muted">Találd meg a számodra legmegfelelőbb szervizt</p>
        </div>
    </div>
    
    <!-- Keresés és szűrés -->
    <div class="card shadow mb-4">
        <div class="card-body">
            <form method="GET" action="">
                <div class="row g-3">
                    <div class="col-md-5">
                        <label for="city" class="form-label">
                            <i class="bi bi-geo-alt"></i> Város
                        </label>
                        <select class="form-select" id="city" name="city">
                            <option value="">Összes város</option>
                            <?php foreach ($cities as $city_item): ?>
                                <option value="<?php echo htmlspecialchars($city_item['city']); ?>"
                                        <?php echo $search_city === $city_item['city'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($city_item['city']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label for="name" class="form-label">
                            <i class="bi bi-wrench"></i> Szerviz neve
                        </label>
                        <input type="text" class="form-control" id="name" name="name" 
                               value="<?php echo htmlspecialchars($search_name); ?>"
                               placeholder="Keresés név alapján...">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search"></i> Keresés
                        </button>
                    </div>
                </div>
                <?php if (!empty($search_city) || !empty($search_name)): ?>
                    <div class="mt-3">
                        <a href="services.php" class="btn btn-secondary btn-sm">
                            <i class="bi bi-x-circle"></i> Szűrők törlése
                        </a>
                        <span class="text-muted ms-2">
                            <?php echo count($services); ?> találat
                        </span>
                    </div>
                <?php endif; ?>
            </form>
        </div>
    </div>
    
    <?php if (empty($services)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Jelenleg nincsenek jóváhagyott szervizek.
            <?php if (is_logged_in()): ?>
                <a href="service_add.php">Légy te az első, aki feltölt egyet!</a>
            <?php endif; ?>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($services as $service): ?>
                <div class="col-md-6 col-lg-4 mb-4">
                    <div class="card h-100 <?php echo $service['is_premium'] == 1 ? 'premium-card' : ''; ?>">
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
                                <i class="bi bi-wrench"></i>
                                <?php echo htmlspecialchars($service['name']); ?>
                                <?php if ($service['is_premium'] == 1): ?>
                                    <span class="badge bg-warning text-dark ms-2">
                                        <i class="bi bi-star-fill"></i> Prémium Partner
                                    </span>
                                <?php endif; ?>
                            </h5>
                            
                            <p class="service-card-text mb-2">
                                <i class="bi bi-geo-alt-fill text-danger"></i> 
                                <strong><?php echo htmlspecialchars($service['city']); ?></strong>
                            </p>
                            
                            <p class="service-card-text mb-2">
                                <i class="bi bi-pin-map"></i> 
                                <?php echo htmlspecialchars($service['address']); ?>
                            </p>
                            
                            <p class="service-card-text mb-2">
                                <i class="bi bi-telephone-fill"></i> 
                                <?php echo htmlspecialchars($service['phone']); ?>
                            </p>
                            
                            <p class="service-card-text mb-2">
                                <i class="bi bi-clock"></i> 
                                <?php echo htmlspecialchars($service['hours']); ?>
                            </p>
                            
                            <!-- Értékelés -->
                            <div class="mb-3">
                                <?php
                                $avg_rating = $service['average_rating'] ?? 0;
                                $rating_count = $service['rating_count'] ?? 0;
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= floor($avg_rating)) {
                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                    } elseif ($i <= ceil($avg_rating) && $avg_rating > floor($avg_rating)) {
                                        echo '<i class="bi bi-star-half text-warning"></i>';
                                    } else {
                                        echo '<i class="bi bi-star text-muted"></i>';
                                    }
                                }
                                ?>
                                <small class="text-muted ms-1">
                                    <?php echo number_format($avg_rating, 1); ?> (<?php echo $rating_count; ?>)
                                </small>
                            </div>
                            
                            <a href="service_view.php?id=<?php echo $service['id']; ?>" 
                               class="btn btn-primary w-100">
                                <i class="bi bi-eye"></i> Részletek és Értékelések
                            </a>
                        </div>
                        
                        <div class="card-footer text-muted small">
                            <i class="bi bi-person"></i> Feltöltötte: <?php echo htmlspecialchars($service['user_name']); ?>
                            <br>
                            <i class="bi bi-calendar"></i> <?php echo date('Y.m.d', strtotime($service['created_at'])); ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
