<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $hours = trim($_POST['hours'] ?? '');
    
    // Validáció
    if (empty($name) || empty($city) || empty($address) || empty($description) || empty($phone) || empty($hours)) {
        $error = 'Minden mező kitöltése kötelező!';
    } else {
        $image_name = null;
        
        // Kép feltöltés kezelése
        if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            $file_type = $_FILES['image']['type'];
            $file_size = $_FILES['image']['size'];
            
            if (!in_array($file_type, $allowed_types)) {
                $error = 'Csak JPG, PNG és GIF képek engedélyezettek!';
            } elseif ($file_size > $max_size) {
                $error = 'A kép mérete maximum 5MB lehet!';
            } else {
                // Egyedi fájlnév generálása
                $extension = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
                $image_name = time() . '_' . uniqid() . '.' . $extension;
                $upload_path = 'uploads/' . $image_name;
                
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $error = 'Hiba történt a kép feltöltése során!';
                    $image_name = null;
                }
            }
        }
        
        // Ha nincs hiba, mentés az adatbázisba
        if (empty($error)) {
            $pdo = getDB();
            $stmt = $pdo->prepare("
                INSERT INTO services (user_id, name, city, address, description, phone, hours, image, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            if ($stmt->execute([
                get_current_user_id(),
                $name,
                $city,
                $address,
                $description,
                $phone,
                $hours,
                $image_name
            ])) {
                $success = 'Szerviz sikeresen feltöltve! Adminisztrátori jóváhagyásra vár.';
                header('refresh:2;url=services.php');
            } else {
                $error = 'Hiba történt a szerviz mentése során!';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">
                        <i class="bi bi-plus-circle"></i> Új Szerviz Hozzáadása
                    </h3>
                </div>
                <div class="card-body p-4">
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="name" class="form-label">
                                <i class="bi bi-wrench"></i> Szerviz neve *
                            </label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="city" class="form-label">
                                    <i class="bi bi-geo-alt"></i> Város *
                                </label>
                                <input type="text" class="form-control" id="city" name="city" 
                                       value="<?php echo htmlspecialchars($_POST['city'] ?? ''); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="phone" class="form-label">
                                    <i class="bi bi-telephone"></i> Telefonszám *
                                </label>
                                <input type="text" class="form-control" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" 
                                       placeholder="+36 1 234 5678" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">
                                <i class="bi bi-pin-map"></i> Cím *
                            </label>
                            <input type="text" class="form-control" id="address" name="address" 
                                   value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>" 
                                   placeholder="1117 Budapest, Irinyi József utca 4-20" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="hours" class="form-label">
                                <i class="bi bi-clock"></i> Nyitvatartás *
                            </label>
                            <input type="text" class="form-control" id="hours" name="hours" 
                                   value="<?php echo htmlspecialchars($_POST['hours'] ?? ''); ?>" 
                                   placeholder="H-P: 8:00-18:00, Szo: 9:00-13:00" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">
                                <i class="bi bi-card-text"></i> Leírás *
                            </label>
                            <textarea class="form-control" id="description" name="description" rows="5" 
                                      required><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                            <small class="text-muted">Írd le a szerviz szolgáltatásait, specialitásait.</small>
                        </div>
                        
                        <div class="mb-4">
                            <label for="image" class="form-label">
                                <i class="bi bi-image"></i> Kép (opcionális)
                            </label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">Maximum 5MB, JPG, PNG vagy GIF formátum</small>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i> 
                            A feltöltött szerviz adminisztrátori jóváhagyásra vár, mielőtt megjelenik a listában.
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="bi bi-upload"></i> Szerviz Feltöltése
                            </button>
                            <a href="services.php" class="btn btn-secondary">
                                <i class="bi bi-x-circle"></i> Mégse
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
