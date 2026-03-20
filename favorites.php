<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$pdo = getDB();

// Kedvenc szervizek lekérdezése
$stmt = $pdo->prepare("
    SELECT s.*, u.name as user_name 
    FROM services s 
    INNER JOIN favorites f ON s.id = f.service_id
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE f.user_id = ? AND s.status = 'approved'
    ORDER BY f.created_at DESC
");
$stmt->execute([get_current_user_id()]);
$favorites = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row mb-4">
        <div class="col-md-12">
            <h1>
                <i class="bi bi-heart-fill text-danger"></i> Kedvenc Szervizeim
            </h1>
            <p class="lead text-muted">Az általad mentett szervizek</p>
        </div>
    </div>
    
    <?php if (empty($favorites)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Még nincsenek kedvenc szervizeid.
            <a href="services.php">Böngéssz a szervizek között</a> és add hozzá őket a kedvenceidhez!
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($favorites as $service): ?>
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
                                        <i class="bi bi-star-fill"></i> Prémium
                                    </span>
                                <?php endif; ?>
                            </h5>
                            
                            <p class="service-card-text mb-2">
                                <i class="bi bi-geo-alt-fill text-danger"></i> 
                                <strong><?php echo htmlspecialchars($service['city']); ?></strong>
                            </p>
                            
                            <p class="service-card-text mb-2">
                                <i class="bi bi-telephone-fill"></i> 
                                <?php echo htmlspecialchars($service['phone']); ?>
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
                            
                            <div class="d-grid gap-2">
                                <a href="service_view.php?id=<?php echo $service['id']; ?>" 
                                   class="btn btn-primary">
                                    <i class="bi bi-eye"></i> Részletek
                                </a>
                                <form method="POST" action="toggle_favorite.php">
                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                    <input type="hidden" name="return_url" value="favorites.php">
                                    <button type="submit" class="btn btn-outline-danger w-100">
                                        <i class="bi bi-heart-fill"></i> Eltávolítás
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php include 'includes/footer.php'; ?>
