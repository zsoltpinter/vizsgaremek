<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';

require_admin();

$pdo = getDB();
$success = '';
$error = '';

// Válasz küldése
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    $user_id = $_POST['user_id'] ?? 0;
    $message = trim($_POST['reply_message'] ?? '');
    
    if (empty($message)) {
        $error = 'Az üzenet nem lehet üres!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO support (user_id, admin_id, message, from_admin) VALUES (?, ?, ?, 1)");
        if ($stmt->execute([$user_id, get_current_user_id(), $message])) {
            $success = 'Válasz elküldve!';
        } else {
            $error = 'Hiba történt az üzenet küldése során!';
        }
    }
}

// Beszélgetés lezárása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['close_conversation'])) {
    $user_id = $_POST['user_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE support SET status = 'closed' WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $success = 'Beszélgetés lezárva!';
}

// Beszélgetés újranyitása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reopen_conversation'])) {
    $user_id = $_POST['user_id'] ?? 0;
    
    $stmt = $pdo->prepare("UPDATE support SET status = 'open' WHERE user_id = ?");
    $stmt->execute([$user_id]);
    $success = 'Beszélgetés újranyitva!';
}

// Kiválasztott user (ha van)
$selected_user_id = $_GET['user_id'] ?? ($_POST['user_id'] ?? null);

// Összes user akinek van support üzenete (csoportosítva)
$stmt = $pdo->query("
    SELECT 
        u.id,
        u.name,
        u.email,
        COUNT(s.id) as message_count,
        MAX(s.created_at) as last_message,
        (SELECT status FROM support WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as conversation_status,
        (SELECT message FROM support WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_message_text,
        (SELECT from_admin FROM support WHERE user_id = u.id ORDER BY created_at DESC LIMIT 1) as last_from_admin
    FROM users u
    INNER JOIN support s ON u.id = s.user_id
    GROUP BY u.id, u.name, u.email
    ORDER BY last_message DESC
");
$conversations = $stmt->fetchAll();

// Ha van kiválasztott user, lekérjük az üzeneteit
$messages = [];
$selected_user = null;
$conversation_status = 'open';

if ($selected_user_id) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$selected_user_id]);
    $selected_user = $stmt->fetch();
    
    $stmt = $pdo->prepare("
        SELECT s.*, u.name as admin_name 
        FROM support s 
        LEFT JOIN users u ON s.admin_id = u.id 
        WHERE s.user_id = ? 
        ORDER BY s.created_at ASC
    ");
    $stmt->execute([$selected_user_id]);
    $messages = $stmt->fetchAll();
    
    // Beszélgetés státusza
    if (!empty($messages)) {
        $conversation_status = $messages[0]['status'];
    }
}

include 'header.php';
?>

<div class="container-fluid mt-4 mb-5">
    <h1 class="mb-4">
        <i class="bi bi-chat-dots"></i> Support Kezelés
    </h1>
    
    <?php if ($success): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <?php if ($error): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    
    <div class="row">
        <!-- Beszélgetések listája (bal oldal) -->
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="bi bi-list-ul"></i> Beszélgetések
                        <span class="badge bg-light text-dark"><?php echo count($conversations); ?></span>
                    </h5>
                </div>
                <div class="card-body p-0" style="max-height: 600px; overflow-y: auto;">
                    <?php if (empty($conversations)): ?>
                        <div class="p-4 text-center text-muted">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <p class="mt-2">Nincsenek support üzenetek</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversations as $conv): ?>
                                <a href="?user_id=<?php echo $conv['id']; ?>" 
                                   class="list-group-item list-group-item-action <?php echo $selected_user_id == $conv['id'] ? 'active' : ''; ?>">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-person-circle"></i>
                                                <?php echo htmlspecialchars($conv['name']); ?>
                                            </h6>
                                            <p class="mb-1 small text-truncate" style="max-width: 250px;">
                                                <?php if ($conv['last_from_admin'] == 1): ?>
                                                    <i class="bi bi-reply"></i> <strong>Te:</strong>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars(substr($conv['last_message_text'], 0, 50)); ?>
                                                <?php echo strlen($conv['last_message_text']) > 50 ? '...' : ''; ?>
                                            </p>
                                            <small class="text-muted">
                                                <i class="bi bi-clock"></i>
                                                <?php echo date('Y.m.d H:i', strtotime($conv['last_message'])); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if ($conv['conversation_status'] === 'closed'): ?>
                                                <span class="badge bg-secondary">
                                                    <i class="bi bi-check-circle"></i> Lezárva
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    <i class="bi bi-circle-fill"></i> Aktív
                                                </span>
                                            <?php endif; ?>
                                            <br>
                                            <span class="badge bg-info mt-1">
                                                <?php echo $conv['message_count']; ?> üzenet
                                            </span>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Chat ablak (jobb oldal) -->
        <div class="col-md-8">
            <?php if (!$selected_user): ?>
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-chat-left-dots" style="font-size: 5rem; color: #ccc;"></i>
                        <h4 class="mt-3 text-muted">Válassz egy beszélgetést a bal oldalról</h4>
                    </div>
                </div>
            <?php else: ?>
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="mb-0">
                                    <i class="bi bi-person-circle"></i>
                                    <?php echo htmlspecialchars($selected_user['name']); ?>
                                </h5>
                                <small><?php echo htmlspecialchars($selected_user['email']); ?></small>
                            </div>
                            <div>
                                <?php if ($conversation_status === 'closed'): ?>
                                    <span class="badge bg-secondary me-2">
                                        <i class="bi bi-check-circle"></i> Lezárva
                                    </span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                                        <button type="submit" name="reopen_conversation" class="btn btn-sm btn-light">
                                            <i class="bi bi-arrow-clockwise"></i> Újranyitás
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge bg-success me-2">
                                        <i class="bi bi-circle-fill"></i> Aktív
                                    </span>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                                        <button type="submit" name="close_conversation" class="btn btn-sm btn-light"
                                                onclick="return confirm('Biztosan lezárod ezt a beszélgetést?')">
                                            <i class="bi bi-check-circle"></i> Lezárás
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Üzenetek -->
                    <div class="card-body" id="messages-container" style="height: 450px; overflow-y: auto;">
                        <?php foreach ($messages as $msg): ?>
                            <?php if ($msg['from_admin'] == 0): ?>
                                <!-- User üzenet (bal oldal) -->
                                <div class="d-flex justify-content-start mb-3 chat-message-left">
                                    <div style="max-width: 70%;">
                                        <div class="chat-bubble-user p-3">
                                            <strong>
                                                <i class="bi bi-person-circle"></i>
                                                <?php echo htmlspecialchars($selected_user['name']); ?>
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
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    
                    <!-- Válasz form -->
                    <div class="card-footer">
                        <?php if ($conversation_status === 'closed'): ?>
                            <div class="alert alert-secondary mb-0">
                                <i class="bi bi-info-circle"></i>
                                Ez a beszélgetés le van zárva. Nyisd újra ha válaszolni szeretnél.
                            </div>
                        <?php else: ?>
                            <form method="POST" action="">
                                <input type="hidden" name="user_id" value="<?php echo $selected_user_id; ?>">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="reply_message" 
                                           placeholder="Írj egy választ..." required>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="bi bi-send-fill"></i> Válasz Küldése
                                    </button>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
// Auto-scroll az aljára
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    if (container) {
        container.scrollTop = container.scrollHeight;
    }
});
</script>

<?php include 'footer.php'; ?>
