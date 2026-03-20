<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

$service_id = $_GET['id'] ?? 0;
$pdo = getDB();

// Szerviz lekérdezése
$stmt = $pdo->prepare("
    SELECT s.*, u.name as user_name 
    FROM services s 
    LEFT JOIN users u ON s.user_id = u.id 
    WHERE s.id = ?
");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: services.php');
    exit;
}

// Megtekintések számának növelése
$stmt_update = $pdo->prepare("UPDATE services SET views = views + 1 WHERE id = ?");
$stmt_update->execute([$service_id]);

// Kommentek lekérdezése
$stmt = $pdo->prepare("
    SELECT c.*, u.name as user_name 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.service_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$service_id]);
$comments = $stmt->fetchAll();

// User értékelése (ha be van jelentkezve)
$user_rating = null;
$is_favorite = false;
if (is_logged_in()) {
    $stmt = $pdo->prepare("SELECT rating FROM ratings WHERE service_id = ? AND user_id = ?");
    $stmt->execute([$service_id, get_current_user_id()]);
    $rating_row = $stmt->fetch();
    if ($rating_row) {
        $user_rating = $rating_row['rating'];
    }
    
    // Kedvenc ellenőrzés
    $stmt = $pdo->prepare("SELECT id FROM favorites WHERE user_id = ? AND service_id = ?");
    $stmt->execute([get_current_user_id(), $service_id]);
    $is_favorite = $stmt->fetch() !== false;
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <!-- Vissza gomb és kedvenc -->
    <div class="mb-3 d-flex justify-content-between align-items-center">
        <a href="services.php" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Vissza a szervizekhez
        </a>
        
        <?php if (is_logged_in()): ?>
            <form method="POST" action="toggle_favorite.php" style="display: inline;">
                <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                <button type="submit" class="btn btn-<?php echo $is_favorite ? 'danger' : 'outline-danger'; ?>">
                    <i class="bi bi-heart<?php echo $is_favorite ? '-fill' : ''; ?>"></i>
                    <?php echo $is_favorite ? 'Kedvencekből eltávolítás' : 'Kedvencekhez adás'; ?>
                </button>
            </form>
        <?php endif; ?>
    </div>
    
    <!-- Szerviz részletek -->
    <div class="card shadow mb-4">
        <div class="row g-0">
            <div class="col-md-5">
                <?php if ($service['image']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($service['image']); ?>" 
                         class="img-fluid rounded-start" style="height: 100%; object-fit: cover;" 
                         alt="<?php echo htmlspecialchars($service['name']); ?>">
                <?php else: ?>
                    <div class="bg-secondary d-flex align-items-center justify-content-center rounded-start" 
                         style="height: 100%; min-height: 300px;">
                        <i class="bi bi-image" style="font-size: 5rem; color: #fff;"></i>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-md-7">
                <div class="card-body p-4">
                    <h2 class="card-title mb-3">
                        <i class="bi bi-wrench-adjustable text-warning"></i>
                        <?php echo htmlspecialchars($service['name']); ?>
                    </h2>
                    
                    <div class="mb-3">
                        <?php if ($service['status'] === 'approved'): ?>
                            <span class="badge badge-approved">
                                <i class="bi bi-check-circle"></i> Jóváhagyva
                            </span>
                        <?php else: ?>
                            <span class="badge badge-pending">
                                <i class="bi bi-clock"></i> Jóváhagyásra vár
                            </span>
                        <?php endif; ?>
                        
                        <?php if ($service['is_premium'] == 1): ?>
                            <span class="badge bg-warning text-dark">
                                <i class="bi bi-star-fill"></i> Prémium Partner
                            </span>
                        <?php endif; ?>
                        
                        <span class="badge bg-info">
                            <i class="bi bi-eye"></i> <?php echo number_format($service['views']); ?> megtekintés
                        </span>
                    </div>
                    
                    <!-- Értékelés megjelenítése -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center">
                            <div class="me-3">
                                <?php
                                $avg_rating = $service['average_rating'] ?? 0;
                                $rating_count = $service['rating_count'] ?? 0;
                                
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= floor($avg_rating)) {
                                        echo '<i class="bi bi-star-fill text-warning"></i>';
                                    } elseif ($i <= ceil($avg_rating) && $avg_rating > floor($avg_rating)) {
                                        echo '<i class="bi bi-star-half text-warning"></i>';
                                    } else {
                                        echo '<i class="bi bi-star text-warning"></i>';
                                    }
                                }
                                ?>
                            </div>
                            <strong><?php echo number_format($avg_rating, 1); ?></strong>
                            <span class="text-muted ms-2">(<?php echo $rating_count; ?> értékelés)</span>
                        </div>
                        
                        <!-- Értékelés form (ha be van jelentkezve) -->
                        <?php if (is_logged_in()): ?>
                            <div class="mt-2">
                                <small class="text-muted">Értékeld te is:</small>
                                <form method="POST" action="rate_service.php" class="d-inline ms-2">
                                    <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                                    <div class="btn-group btn-group-sm" role="group">
                                        <?php for ($i = 1; $i <= 5; $i++): ?>
                                            <button type="submit" name="rating" value="<?php echo $i; ?>" 
                                                    class="btn btn-outline-warning <?php echo $user_rating == $i ? 'active' : ''; ?>">
                                                <i class="bi bi-star-fill"></i> <?php echo $i; ?>
                                            </button>
                                        <?php endfor; ?>
                                    </div>
                                </form>
                                <?php if ($user_rating): ?>
                                    <small class="text-success ms-2">
                                        <i class="bi bi-check-circle"></i> Te <?php echo $user_rating; ?> csillagot adtál
                                    </small>
                                <?php endif; ?>
                            </div>
                        <?php else: ?>
                            <div class="mt-2">
                                <small class="text-muted">
                                    <a href="login.php">Jelentkezz be</a> az értékeléshez
                                </small>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="mb-3">
                        <h5><i class="bi bi-info-circle"></i> Leírás</h5>
                        <p class="text-muted"><?php echo nl2br(htmlspecialchars($service['description'])); ?></p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-geo-alt-fill text-danger"></i> Város</h6>
                            <p><?php echo htmlspecialchars($service['city']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-pin-map"></i> Cím</h6>
                            <p><?php echo htmlspecialchars($service['address']); ?></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-telephone-fill"></i> Telefonszám</h6>
                            <p><a href="tel:<?php echo htmlspecialchars($service['phone']); ?>">
                                <?php echo htmlspecialchars($service['phone']); ?>
                            </a></p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <h6><i class="bi bi-clock"></i> Nyitvatartás</h6>
                            <p><?php echo htmlspecialchars($service['hours']); ?></p>
                        </div>
                    </div>
                    
                    <div class="text-muted small mb-3">
                        <i class="bi bi-person"></i> Feltöltötte: <?php echo htmlspecialchars($service['user_name']); ?>
                        <br>
                        <i class="bi bi-calendar"></i> <?php echo date('Y.m.d H:i', strtotime($service['created_at'])); ?>
                    </div>
                    
                    <!-- Időpont foglalás gomb -->
                    <?php if (is_logged_in()): ?>
                        <div class="d-grid">
                            <a href="booking_create.php?service_id=<?php echo $service_id; ?>" class="btn btn-success btn-lg">
                                <i class="bi bi-calendar-check"></i> Időpont foglalása
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info mb-0">
                            <i class="bi bi-info-circle"></i> 
                            <a href="login.php">Jelentkezz be</a> az időpont foglalásához!
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kommentek szekció -->
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">
                <i class="bi bi-chat-left-text"></i> Értékelések és Vélemények
                <span class="badge bg-light text-dark"><?php echo count($comments); ?></span>
            </h4>
        </div>
        <div class="card-body">
            <!-- Komment hozzáadása form (csak bejelentkezve) -->
            <?php if (is_logged_in()): ?>
                <div class="mb-4">
                    <h5>Írj véleményt</h5>
                    <form method="POST" action="comment_add.php">
                        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                        <div class="mb-3">
                            <textarea class="form-control" name="comment" rows="3" 
                                      placeholder="Oszd meg tapasztalataidat erről a szervizről..." required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-send"></i> Vélemény Küldése
                        </button>
                    </form>
                </div>
                <hr>
            <?php else: ?>
                <div class="alert alert-info mb-4">
                    <i class="bi bi-info-circle"></i> 
                    <a href="login.php">Jelentkezz be</a> hogy véleményt írhass!
                </div>
            <?php endif; ?>
            
            <!-- Kommentek listája -->
            <?php if (empty($comments)): ?>
                <p class="text-muted text-center py-4">
                    <i class="bi bi-chat-left-dots" style="font-size: 3rem;"></i>
                    <br>
                    Még nincsenek értékelések. Légy te az első!
                </p>
            <?php else: ?>
                <?php foreach ($comments as $comment): ?>
                    <div class="border-bottom pb-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    <i class="bi bi-person-circle"></i>
                                    <?php echo htmlspecialchars($comment['user_name']); ?>
                                </h6>
                                <small class="text-muted">
                                    <i class="bi bi-clock"></i>
                                    <?php echo date('Y.m.d H:i', strtotime($comment['created_at'])); ?>
                                </small>
                            </div>
                            <?php if (is_admin()): ?>
                                <form method="POST" action="admin/comments.php" style="display: inline;">
                                    <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                                    <input type="hidden" name="return_url" value="<?php echo $_SERVER['REQUEST_URI']; ?>">
                                    <button type="submit" class="btn btn-danger btn-sm" 
                                            onclick="return confirm('Biztosan törölni szeretnéd ezt a kommentet?')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        <p class="mt-2 mb-0"><?php echo nl2br(htmlspecialchars($comment['comment'])); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
