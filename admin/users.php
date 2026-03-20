<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();

// Összes felhasználó lekérdezése
$stmt = $pdo->query("
    SELECT u.*,
           (SELECT COUNT(*) FROM services WHERE user_id = u.id) as services_count,
           (SELECT COUNT(*) FROM comments WHERE user_id = u.id) as comments_count
    FROM users u
    ORDER BY u.created_at DESC
");
$users = $stmt->fetchAll();

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <h1 class="mb-4">
        <i class="bi bi-people"></i> Felhasználók
    </h1>
    
    <?php if (empty($users)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Nincsenek felhasználók az adatbázisban.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-people-fill"></i> Összes Felhasználó
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Név</th>
                                <th>Email</th>
                                <th>Szerepkör</th>
                                <th>Szervizek</th>
                                <th>Kommentek</th>
                                <th>Regisztráció</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo $user['id']; ?></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td>
                                        <?php if ($user['is_admin'] == 1): ?>
                                            <span class="badge bg-danger">
                                                <i class="bi bi-shield-lock"></i> Admin
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-primary">
                                                <i class="bi bi-person"></i> User
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <?php echo $user['services_count']; ?> szerviz
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">
                                            <?php echo $user['comments_count']; ?> komment
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo date('Y.m.d H:i', strtotime($user['created_at'])); ?></small>
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
                <div class="col-md-4">
                    <div class="card bg-primary text-white">
                        <div class="card-body">
                            <h5><i class="bi bi-people"></i> Összes Felhasználó</h5>
                            <h2><?php echo count($users); ?></h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-danger text-white">
                        <div class="card-body">
                            <h5><i class="bi bi-shield-lock"></i> Admin Felhasználók</h5>
                            <h2>
                                <?php 
                                echo count(array_filter($users, function($u) { 
                                    return $u['is_admin'] == 1; 
                                })); 
                                ?>
                            </h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white">
                        <div class="card-body">
                            <h5><i class="bi bi-person-check"></i> Normál Felhasználók</h5>
                            <h2>
                                <?php 
                                echo count(array_filter($users, function($u) { 
                                    return $u['is_admin'] == 0; 
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
