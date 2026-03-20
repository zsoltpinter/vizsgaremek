<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();
$success = '';

// Komment törlése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_comment_id'])) {
    $comment_id = $_POST['delete_comment_id'] ?? 0;
    $return_url = $_POST['return_url'] ?? 'comments.php';
    
    if ($comment_id > 0) {
        $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
        $stmt->execute([$comment_id]);
        
        // Ha van return URL (service_view.php-ról jött), oda irányít vissza
        if (strpos($return_url, 'service_view.php') !== false) {
            header('Location: .' . $return_url);
            exit;
        }
        
        $success = 'Komment sikeresen törölve!';
    }
}

// Összes komment lekérdezése
$stmt = $pdo->query("
    SELECT c.*, 
           u.name as user_name,
           s.name as service_name,
           s.id as service_id
    FROM comments c
    LEFT JOIN users u ON c.user_id = u.id
    LEFT JOIN services s ON c.service_id = s.id
    ORDER BY c.created_at DESC
");
$comments = $stmt->fetchAll();

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <h1 class="mb-4">
        <i class="bi bi-chat-left-text"></i> Kommentek Moderálása
    </h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if (empty($comments)): ?>
        <div class="alert alert-info">
            <i class="bi bi-info-circle"></i> Még nincsenek kommentek az adatbázisban.
        </div>
    <?php else: ?>
        <div class="card">
            <div class="card-header bg-primary text-white">
                <i class="bi bi-chat-left-text-fill"></i> Összes Komment
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>Komment</th>
                                <th>Szerviz</th>
                                <th>Felhasználó</th>
                                <th>Dátum</th>
                                <th>Műveletek</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($comments as $comment): ?>
                                <tr>
                                    <td><?php echo $comment['id']; ?></td>
                                    <td>
                                        <div style="max-width: 400px;">
                                            <?php 
                                            $text = htmlspecialchars($comment['comment']);
                                            echo strlen($text) > 150 ? substr($text, 0, 150) . '...' : $text;
                                            ?>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="../service_view.php?id=<?php echo $comment['service_id']; ?>" 
                                           target="_blank">
                                            <?php echo htmlspecialchars($comment['service_name']); ?>
                                            <i class="bi bi-box-arrow-up-right"></i>
                                        </a>
                                    </td>
                                    <td><?php echo htmlspecialchars($comment['user_name']); ?></td>
                                    <td>
                                        <small><?php echo date('Y.m.d H:i', strtotime($comment['created_at'])); ?></small>
                                    </td>
                                    <td>
                                        <form method="POST" action="" style="display: inline;">
                                            <input type="hidden" name="delete_comment_id" value="<?php echo $comment['id']; ?>">
                                            <button type="submit" class="btn btn-danger btn-sm" 
                                                    onclick="return confirm('Biztosan törölni szeretnéd ezt a kommentet?')">
                                                <i class="bi bi-trash"></i> Törlés
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="mt-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5><i class="bi bi-chat-dots"></i> Összes Komment</h5>
                    <h2><?php echo count($comments); ?></h2>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
