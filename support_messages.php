<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$pdo = getDB();

// Üzenetek lekérdezése - JAVÍTOTT admin névvel
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
$stmt->execute([get_current_user_id()]);
$messages = $stmt->fetchAll();

// Csak az üzenetek HTML-je, header/footer nélkül
if (empty($messages)): ?>
    <div class="text-center text-muted py-5">
        <i class="bi bi-chat-left-dots" style="font-size: 4rem;"></i>
        <p class="mt-3">Még nincsenek üzenetek. Írj egy üzenetet a support csapatnak!</p>
    </div>
<?php else: ?>
    <?php foreach ($messages as $msg): ?>
        <?php if ($msg['from_admin'] == 0): ?>
            <!-- User üzenet (jobb oldal) -->
            <div class="d-flex justify-content-end mb-3">
                <div style="max-width: 70%;">
                    <div class="user-message-bubble p-3">
                        <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i>
                        <?php echo date('Y.m.d H:i', strtotime($msg['created_at'])); ?>
                    </small>
                </div>
            </div>
        <?php else: ?>
            <!-- Admin üzenet (bal oldal) -->
            <div class="d-flex justify-content-start mb-3">
                <div style="max-width: 70%;">
                    <div class="admin-message-bubble p-3">
                        <strong>
                            <i class="bi bi-shield-check"></i> 
                            <?php echo htmlspecialchars($msg['admin_name'] ?? 'Admin'); ?>
                        </strong>
                        <p class="mb-0 mt-2"><?php echo nl2br(htmlspecialchars($msg['message'])); ?></p>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i>
                        <?php echo date('Y.m.d H:i', strtotime($msg['created_at'])); ?>
                    </small>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
<?php endif; ?>
