<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

// Ha már be van jelentkezve, átirányítás
if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Minden mező kitöltése kötelező!';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            // Sikeres bejelentkezés
            login_user($user);
            header('Location: index.php');
            exit;
        } else {
            $error = 'Hibás email cím vagy jelszó!';
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h2 class="text-center mb-4">
                        <i class="bi bi-box-arrow-in-right text-warning"></i>
                        Bejelentkezés
                    </h2>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email cím</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Jelszó</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">
                            <i class="bi bi-box-arrow-in-right"></i> Bejelentkezés
                        </button>
                        
                        <div class="text-center">
                            <p class="mb-0">Még nincs fiókod? <a href="register.php">Regisztrálj itt</a></p>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="card shadow mt-3">
                <div class="card-body text-center">
                    <h6 class="mb-2">Admin bejelentkezés</h6>
                    <a href="admin/login.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-shield-lock"></i> Admin Panel
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
