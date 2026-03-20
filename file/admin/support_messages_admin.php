<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();
$user_id = $_GET['user_id'] ?? 0;

if (!$user_id) {
    exit('Invalid user ID');
}

// Üzenetek lekérdezése JAVÍTOTT admin névvel
$stmt = $pdo->prepare("
    SELECT s.*, 
           admin_u.name as admin_name,
           user_u.name as user_name
    FROM support s 
    LEFT JOIN users admin_u ON s.admin_id = admin_u.id 
    LEFT JOIN users user_u ON s.user_id = user_u.id
    WHERE s.user_id = ? 
    ORDER BY s.created_at ASC
");
$stmt->execute([$user_id]);
$messages = $stmt->fetchAll();

// Csak az üzenetek HTML-je
foreach ($messages as $msg):
    if ($msg['from_admin'] == 0): ?>
        <!-- User üzenet (bal oldal) -->
        <div class="d-flex justify-content-start mb-3 chat-message-left">
            <div style="max-width: 70%;">
                <div class="chat-bubble-user p-3">
                    <strong>
                        <i class="bi bi-person-circle"></i>
                        <?php echo htmlspecialchars($msg['user_name']); ?>
                    </strong>
                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                </div>
                <small class="chat-timestamp ms-2">
                    <i class="bi bi-clock"></i>
                    <?php echo date('Y.m.d H:i', strtotime($msg['created_at'])); ?>
                </small>
            </div>
        </div>
    <?php else: ?>
        <!-- Admin üzenet (jobb oldal) -->
        <div class="d-flex justify-content-end mb-3 chat-message-right">
            <div style="max-width: 70%;">
                <div class="chat-bubble-admin p-3">
                    <strong>
                        <i class="bi bi-shield-check-fill"></i>
                        <?php echo htmlspecialchars($msg['admin_name'] ?? 'Admin'); ?>
                    </strong>
                    <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                </div>
                <small class="chat-timestamp me-2">
                    <i class="bi bi-clock"></i>
                    <?php echo date('Y.m.d H:i', strtotime($msg['created_at'])); ?>
                </small>
            </div>
        </div>
    <?php endif;
endforeach;
?>