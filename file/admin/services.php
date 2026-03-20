<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();
$success = '';

// Összes szerviz lekérdezése
$stmt = $pdo->query("
    SELECT s.*, u.name as user_name 
    FROM services s 
    LEFT JOIN users u ON s.user_id = u.id 
    ORDER BY 
        CASE WHEN s.status = 'pending' THEN 0 ELSE 1 END,
        s.created_at DESC
");
$services = $stmt->fetchAll();

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-list-check"></i> Szervizek Kezelése
        </h1>
    </div>
    
    <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i>
            <?php
            if ($_GET['success'] == 'approved') echo 'Szerviz sikeresen jóváhagyva!';
            elseif ($_GET['success'] == 'deleted') echo 'Szerviz sikeresen törölve!';
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (empty($services)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Még nincsenek szervizek az adatbázisban.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-list-check"></i> Összes Szerviz
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Név</th>
                                <th>Város</th>
                                <th>Cím</th>
                                <th>Feltöltő</th>
                                <th>Státusz</th>
                                <th>Dátum</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($services as $service): ?>
                                <tr>
                                    <td><?php echo $service['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($service['name']); ?></strong>
                                        <?php if ($service['image']): ?>
                                            <br><small class="text-muted">
                                                <i class="bi bi-image"></i> Van kép
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($service['city']); ?></td>
                                    <td><?php echo htmlspecialchars($service['address']); ?></td>
                                    <td><?php echo htmlspecialchars($service['user_name']); ?></td>
                                    <td>
                                        <?php if ($service['status'] === 'approved'): ?>
                                            <span class="badge bg-success">
                                                <i class="bi bi-check-circle"></i> Jóváhagyva
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-warning text-dark">
                                                <i class="bi bi-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                        <br>
                                        <?php if ($service['is_premium'] == 1): ?>
                                            <span class="badge bg-warning text-dark mt-1">
                                                <i class="bi bi-star-fill"></i> Prémium
                                            </span>
                                        <?php endif; ?>
                                        <br>
                                        <small class="text-muted">
                                            <i class="bi bi-eye"></i> <?php echo $service['views']; ?> megtekintés
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo date('Y.m.d H:i', strtotime($service['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="../service_view.php?id=<?php echo $service['id']; ?>" 
                                               class="btn btn-info" target="_blank" title="Megtekintés">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            
                                            <?php if ($service['status'] === 'pending'): ?>
                                                <form method="POST" action="service_approve.php" style="display: inline;">
                                                    <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                    <button type="submit" class="btn btn-success" title="Jóváhagyás">
                                                        <i class="bi bi-check-lg"></i>
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <form method="POST" action="service_toggle_premium.php" style="display: inline;">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <button type="submit" class="btn btn-warning" 
                                                        title="<?php echo $service['is_premium'] == 1 ? 'Prémium eltávolítása' : 'Prémium hozzáadása'; ?>">
                                                    <i class="bi bi-star<?php echo $service['is_premium'] == 1 ? '-fill' : ''; ?>"></i>
                                                </button>
                                            </form>
                                            
                                            <form method="POST" action="service_delete.php" style="display: inline;">
                                                <input type="hidden" name="service_id" value="<?php echo $service['id']; ?>">
                                                <button type="submit" class="btn btn-danger" 
                                                        onclick="return confirm('Biztosan törölni szeretnéd ezt a szervizt?')" 
                                                        title="Törlés">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <div class="row">
                <div class="col-md-6">
                    <div class="card bg-warning text-dark">
                        <div class="card-body">
                            <h5>
                                <i class="bi bi-clock-history"></i> Pending Szervizek
                            </h5>
                            <h2>
                                <?php 
                                echo count(array_filter($services, function($s) { 
                                    return $s['status'] === 'pending'; 
                                })); 
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5>
                                <i class="bi bi-check-circle"></i> Jóváhagyott Szervizek
                            </h5>
                            <h2>
                                <?php 
                                echo count(array_filter($services, function($s) { 
                                    return $s['status'] === 'approved'; 
                                })); 
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
