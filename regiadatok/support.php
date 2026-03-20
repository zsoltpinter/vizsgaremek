<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$pdo = getDB();
$error = '';
$success = '';

// Üzenet küldése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = trim($_POST['message'] ?? '');
    
    if (empty($message)) {
        $error = 'Az üzenet nem lehet üres!';
    } else {
        $stmt = $pdo->prepare("INSERT INTO support (user_id, message, from_admin) VALUES (?, ?, 0)");
        if ($stmt->execute([get_current_user_id(), $message])) {
            // Redirect ugyanarra az oldalra hogy ne jelenjen meg success üzenet
            header('Location: support.php');
            exit;
        } else {
            $error = 'Hiba történt az üzenet küldése során!';
        }
    }
}

// Üzenetek lekérdezése
$stmt = $pdo->prepare("
    SELECT s.*, u.name as admin_name 
    FROM support s 
    LEFT JOIN users u ON s.admin_id = u.id 
    WHERE s.user_id = ? 
    ORDER BY s.created_at ASC
");
$stmt->execute([get_current_user_id()]);
$messages = $stmt->fetchAll();

include 'includes/header.php';
?>

<style>
/* Dark mode support chat styling */
[data-theme="dark"] #messages-container {
    background-color: #0d1117 !important;
}

[data-theme="dark"] .bg-white {
    background-color: #161b22 !important;
    border-color: #30363d !important;
}

[data-theme="dark"] .bg-white p,
[data-theme="dark"] .bg-white strong {
    color: #e6edf3 !important;
}

[data-theme="dark"] .card {
    background-color: #161b22 !important;
    border-color: #30363d !important;
}

[data-theme="dark"] .card-footer {
    background-color: #161b22 !important;
    border-color: #30363d !important;
}

[data-theme="dark"] .form-control {
    background-color: #0d1117 !important;
    border-color: #30363d !important;
    color: #e6edf3 !important;
}

[data-theme="dark"] .form-control:focus {
    background-color: #0d1117 !important;
    border-color: #3498db !important;
    color: #e6edf3 !important;
}

/* User üzenet buborék - marad kék */
.user-message-bubble {
    background: linear-gradient(135deg, #3498db 0%, #2980b9 100%) !important;
    color: white !important;
    border-radius: 18px !important;
    box-shadow: 0 2px 8px rgba(52, 152, 219, 0.3);
}

/* Admin üzenet buborék - dark mode */
.admin-message-bubble {
    background-color: var(--card-bg) !important;
    border: 1px solid var(--border-color, #ddd) !important;
    border-radius: 18px !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

[data-theme="dark"] .admin-message-bubble {
    background-color: #161b22 !important;
    border-color: #30363d !important;
}

[data-theme="dark"] .admin-message-bubble strong {
    color: #ffc107 !important;
}

[data-theme="dark"] .admin-message-bubble p {
    color: #e6edf3 !important;
}
</style>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-chat-dots"></i> Support Chat
                    </h4>
                    <small>Az üzenetek automatikusan frissülnek 3 másodpercenként</small>
                </div>
                
                <!-- Üzenetek -->
                <div class="card-body" id="messages-container" style="height: 500px; overflow-y: auto; background-color: #f8f9fa;">
                    <?php if (empty($messages)): ?>
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
                </div>
                
                <!-- Üzenet küldés form -->
                <div class="card-footer">
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-sm mb-2">
                            <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" id="message-form">
                        <div class="input-group">
                            <input type="text" class="form-control" name="message" id="message-input"
                                   placeholder="Írj egy üzenetet..." required>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-send"></i> Küldés
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="alert alert-info mt-3">
                <i class="bi bi-info-circle"></i> 
                A support csapat hamarosan válaszol az üzenetedre. Az üzenetek automatikusan frissülnek.
            </div>
        </div>
    </div>
</div>

<script>
// AJAX auto-refresh csak az üzenetekhez (3 másodpercenként)
function loadMessages() {
    fetch('support_messages.php')
        .then(response => response.text())
        .then(html => {
            const container = document.getElementById('messages-container');
            const wasAtBottom = container.scrollHeight - container.scrollTop <= container.clientHeight + 50;
            
            container.innerHTML = html;
            
            // Ha a user az alján volt, maradjon ott
            if (wasAtBottom) {
                container.scrollTop = container.scrollHeight;
            }
        })
        .catch(error => console.error('Hiba az üzenetek betöltésekor:', error));
}

// 3 másodpercenként frissítés
setInterval(loadMessages, 3000);

// Scroll az aljára oldal betöltéskor
document.addEventListener('DOMContentLoaded', function() {
    const container = document.getElementById('messages-container');
    container.scrollTop = container.scrollHeight;
    
    // Form submit után törölje az inputot és frissítse az üzeneteket
    const form = document.getElementById('message-form');
    if (form) {
        form.addEventListener('submit', function(e) {
            // Hagyjuk hogy a form normálisan submitáljon
            setTimeout(function() {
                document.getElementById('message-input').value = '';
                loadMessages();
            }, 500);
        });
    }
});
</script>

<?php include 'includes/footer.php'; ?>
