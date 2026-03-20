<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$pdo = getDB();
$error = '';
$success = '';

// User adatok lekérdezése
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([get_current_user_id()]);
$user = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    
    if (empty($name) || empty($email)) {
        $error = 'Minden mező kitöltése kötelező!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Érvénytelen email cím!';
    } else {
        // Email uniqueness ellenőrzés (kivéve saját email)
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, get_current_user_id()]);
        
        if ($stmt->fetch()) {
            $error = 'Ez az email cím már használatban van!';
        } else {
            // Profil frissítése
            $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
            
            if ($stmt->execute([$name, $email, get_current_user_id()])) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = 'Profil sikeresen frissítve!';
                $user['name'] = $name;
                $user['email'] = $email;
            } else {
                $error = 'Hiba történt a profil frissítése során!';
            }
        }
    }
}

// User szervizei
$stmt = $pdo->prepare("SELECT * FROM services WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([get_current_user_id()]);
$my_services = $stmt->fetchAll();

// Kedvenc szervizek
$stmt = $pdo->prepare("
    SELECT s.*, u.name as uploader_name,
           (SELECT AVG(rating) FROM ratings WHERE service_id = s.id) as avg_rating,
           (SELECT COUNT(*) FROM ratings WHERE service_id = s.id) as rating_count
    FROM favorites f
    JOIN services s ON f.service_id = s.id
    JOIN users u ON s.user_id = u.id
    WHERE f.user_id = ? AND s.status = 'approved'
    ORDER BY f.created_at DESC
");
$stmt->execute([get_current_user_id()]);
$favorite_services = $stmt->fetchAll();

// Foglalások lekérdezése
$stmt = $pdo->prepare("
    SELECT b.*, s.name as service_name, s.city, s.address, s.phone
    FROM bookings b
    JOIN services s ON b.service_id = s.id
    WHERE b.user_id = ?
    ORDER BY b.booking_date DESC, b.booking_time DESC
");
$stmt->execute([get_current_user_id()]);
$my_bookings = $stmt->fetchAll();

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row">
        <!-- Profil szerkesztés -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-person-circle"></i> Profil Adatok
                    </h4>
                </div>
                <div class="card-body">
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
                    
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label for="name" class="form-label">Név</label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo htmlspecialchars($user['name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email cím</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Regisztráció dátuma</label>
                            <input type="text" class="form-control" 
                                   value="<?php echo date('Y.m.d H:i', strtotime($user['created_at'])); ?>" disabled>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-save"></i> Mentés
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Saját szervizek -->
        <div class="col-md-6 mb-4">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-list-ul"></i> Feltöltött Szervizeim
                        <span class="badge bg-light text-dark"><?php echo count($my_services); ?></span>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($my_services)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                            <br>
                            Még nem töltöttél fel szervizeket.
                            <br>
                            <a href="service_add.php" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-circle"></i> Első szerviz hozzáadása
                            </a>
                        </p>
                    <?php else: ?>
                        <div class="list-group">
                            <?php foreach ($my_services as $service): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div class="flex-grow-1">
                                            <h6 class="mb-1">
                                                <i class="bi bi-wrench"></i>
                                                <?php echo htmlspecialchars($service['name']); ?>
                                            </h6>
                                            <p class="mb-1 small text-muted">
                                                <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($service['city']); ?>
                                            </p>
                                            <small class="text-muted">
                                                <?php echo date('Y.m.d', strtotime($service['created_at'])); ?>
                                            </small>
                                        </div>
                                        <div>
                                            <?php if ($service['status'] === 'approved'): ?>
                                                <span class="badge badge-approved">
                                                    <i class="bi bi-check-circle"></i> Jóváhagyva
                                                </span>
                                                <br>
                                                <a href="service_view.php?id=<?php echo $service['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary mt-1">
                                                    <i class="bi bi-eye"></i> Megtekintés
                                                </a>
                                            <?php else: ?>
                                                <span class="badge badge-pending">
                                                    <i class="bi bi-clock"></i> Pending
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Foglalások -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar-check"></i> Foglalt Időpontjaim
                        <span class="badge bg-light text-dark"><?php echo count($my_bookings); ?></span>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($my_bookings)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-calendar-x" style="font-size: 3rem;"></i>
                            <br>
                            Még nincs foglalt időpontod.
                            <br>
                            <a href="services.php" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-search"></i> Szervizek böngészése
                            </a>
                        </p>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Dátum & Idő</th>
                                        <th>Szerviz</th>
                                        <th>Szolgáltatások</th>
                                        <th>Időtartam</th>
                                        <th>Státusz</th>
                                        <th>Műveletek</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($my_bookings as $booking): ?>
                                        <tr>
                                            <td>
                                                <i class="bi bi-calendar3"></i>
                                                <?php echo date('Y.m.d', strtotime($booking['booking_date'])); ?>
                                                <br>
                                                <small><i class="bi bi-clock"></i> <?php echo date('H:i', strtotime($booking['booking_time'])); ?></small>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($booking['service_name']); ?></strong>
                                                <br>
                                                <small class="text-muted">
                                                    <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($booking['city']); ?>
                                                </small>
                                            </td>
                                            <td>
                                                <?php 
                                                if ($booking['services_requested']) {
                                                    $services = json_decode($booking['services_requested'], true);
                                                    if ($services && is_array($services)) {
                                                        echo '<small>';
                                                        foreach ($services as $svc) {
                                                            echo '<i class="bi bi-check-circle text-success"></i> ' . htmlspecialchars($svc) . '<br>';
                                                        }
                                                        echo '</small>';
                                                    } else {
                                                        echo '<small class="text-muted">-</small>';
                                                    }
                                                } else {
                                                    echo '<small class="text-muted">Nincs megadva</small>';
                                                }
                                                ?>
                                            </td>
                                            <td>
                                                <?php if ($booking['estimated_duration']): ?>
                                                    <span class="badge bg-info">
                                                        <i class="bi bi-clock"></i> 
                                                        <?php 
                                                        $hours = floor($booking['estimated_duration'] / 60);
                                                        $mins = $booking['estimated_duration'] % 60;
                                                        if ($hours > 0) {
                                                            echo $hours . ' óra';
                                                            if ($mins > 0) echo ' ' . $mins . ' perc';
                                                        } else {
                                                            echo $mins . ' perc';
                                                        }
                                                        ?>
                                                    </span>
                                                <?php else: ?>
                                                    <small class="text-muted">-</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php
                                                $status_badges = [
                                                    'pending' => '<span class="badge bg-warning"><i class="bi bi-clock"></i> Függőben</span>',
                                                    'confirmed' => '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Visszaigazolva</span>',
                                                    'cancelled' => '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> Lemondva</span>',
                                                    'completed' => '<span class="badge bg-info"><i class="bi bi-check-all"></i> Teljesítve</span>'
                                                ];
                                                echo $status_badges[$booking['status']] ?? '';
                                                ?>
                                            </td>
                                            <td>
                                                <a href="service_view.php?id=<?php echo $booking['service_id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary" title="Szerviz megtekintése">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <?php if ($booking['status'] == 'pending' || $booking['status'] == 'confirmed'): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="cancelBooking(<?php echo $booking['id']; ?>)" 
                                                            title="Foglalás lemondása">
                                                        <i class="bi bi-x-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Kedvenc szervizek -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-heart-fill"></i> Kedvenc Szervizeim
                        <span class="badge bg-light text-dark"><?php echo count($favorite_services); ?></span>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if (empty($favorite_services)): ?>
                        <p class="text-muted text-center py-4">
                            <i class="bi bi-heart" style="font-size: 3rem;"></i>
                            <br>
                            Még nem jelöltél meg kedvenc szervizeket.
                            <br>
                            <a href="services.php" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-search"></i> Szervizek böngészése
                            </a>
                        </p>
                    <?php else: ?>
                        <div class="row">
                            <?php foreach ($favorite_services as $service): ?>
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 <?php echo $service['is_premium'] ? 'premium-card' : ''; ?>">
                                        <?php if ($service['image']): ?>
                                            <img src="uploads/<?php echo htmlspecialchars($service['image']); ?>" 
                                                 class="card-img-top" alt="<?php echo htmlspecialchars($service['name']); ?>"
                                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'card-img-top bg-secondary d-flex align-items-center justify-content-center\' style=\'height: 200px;\'><i class=\'bi bi-image text-white\' style=\'font-size: 3rem;\'></i></div>';">
                                        <?php else: ?>
                                            <div class="card-img-top bg-secondary d-flex align-items-center justify-content-center" 
                                                 style="height: 200px;">
                                                <i class="bi bi-image text-white" style="font-size: 3rem;"></i>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <div class="card-body">
                                            <h5 class="service-card-title">
                                                <?php echo htmlspecialchars($service['name']); ?>
                                                <?php if ($service['is_premium']): ?>
                                                    <span class="badge bg-warning text-dark">
                                                        <i class="bi bi-star-fill"></i> Prémium
                                                    </span>
                                                <?php endif; ?>
                                            </h5>
                                            
                                            <p class="service-card-text mb-2">
                                                <i class="bi bi-geo-alt"></i> 
                                                <?php echo htmlspecialchars($service['city']); ?>, 
                                                <?php echo htmlspecialchars($service['address']); ?>
                                            </p>
                                            
                                            <p class="service-card-text mb-2">
                                                <i class="bi bi-telephone"></i> 
                                                <?php echo htmlspecialchars($service['phone']); ?>
                                            </p>
                                            
                                            <?php if ($service['avg_rating']): ?>
                                                <div class="mb-2">
                                                    <?php
                                                    $avg = round($service['avg_rating'], 1);
                                                    for ($i = 1; $i <= 5; $i++) {
                                                        if ($i <= $avg) {
                                                            echo '<i class="bi bi-star-fill text-warning"></i>';
                                                        } elseif ($i - 0.5 <= $avg) {
                                                            echo '<i class="bi bi-star-half text-warning"></i>';
                                                        } else {
                                                            echo '<i class="bi bi-star text-warning"></i>';
                                                        }
                                                    }
                                                    ?>
                                                    <small class="text-muted">
                                                        (<?php echo $avg; ?> - <?php echo $service['rating_count']; ?> értékelés)
                                                    </small>
                                                </div>
                                            <?php endif; ?>
                                            
                                            <div class="d-flex gap-2">
                                                <a href="service_view.php?id=<?php echo $service['id']; ?>" 
                                                   class="btn btn-primary btn-sm flex-grow-1">
                                                    <i class="bi bi-eye"></i> Megtekintés
                                                </a>
                                                <button class="btn btn-danger btn-sm" 
                                                        onclick="toggleFavorite(<?php echo $service['id']; ?>, this)">
                                                    <i class="bi bi-heart-fill"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleFavorite(serviceId, button) {
    fetch('toggle_favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'service_id=' + serviceId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Eltávolítjuk a kártyát az oldalról
            button.closest('.col-md-4').remove();
            
            // Ha nincs több kedvenc, frissítjük az oldalt
            const remainingCards = document.querySelectorAll('.col-md-4').length;
            if (remainingCards === 0) {
                location.reload();
            } else {
                // Frissítjük a badge számot
                const badge = document.querySelector('.card-header .badge');
                if (badge) {
                    badge.textContent = remainingCards;
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}

function cancelBooking(bookingId) {
    if (!confirm('Biztosan lemondod ezt a foglalást?')) {
        return;
    }
    
    fetch('booking_cancel.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'booking_id=' + bookingId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        } else {
            alert('Hiba történt a lemondás során!');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Hiba történt a lemondás során!');
    });
}
</script>

<?php include 'includes/footer.php'; ?>
