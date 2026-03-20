<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

require_login();

$pdo = getDB();
$error = '';
$success = '';

// Szerviz ID ellenőrzése
$service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : 0;

if (!$service_id) {
    header('Location: services.php');
    exit;
}

// Szerviz adatok lekérdezése
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ? AND status = 'approved'");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    header('Location: services.php');
    exit;
}

// Szolgáltatás típusok lekérdezése
try {
    $stmt = $pdo->query("SELECT * FROM service_types ORDER BY name");
    $service_types = $stmt->fetchAll();
} catch (PDOException $e) {
    // Ha a tábla nem létezik, alapértelmezett szolgáltatásokat használunk
    $service_types = [
        ['id' => 1, 'name' => 'Olajcsere', 'duration_minutes' => 30, 'description' => 'Motor olaj és olajszűrő csere'],
        ['id' => 2, 'name' => 'Fékjavítás', 'duration_minutes' => 90, 'description' => 'Fékbetét, féktárcsa csere és beállítás'],
        ['id' => 3, 'name' => 'Futómű javítás', 'duration_minutes' => 120, 'description' => 'Futómű alkatrészek cseréje és beállítás'],
        ['id' => 4, 'name' => 'Motorjavítás', 'duration_minutes' => 240, 'description' => 'Motor diagnosztika és javítás'],
        ['id' => 5, 'name' => 'Klíma szerviz', 'duration_minutes' => 60, 'description' => 'Klíma tisztítás, töltés'],
        ['id' => 6, 'name' => 'Gumiabroncs csere', 'duration_minutes' => 45, 'description' => 'Négy kerék gumiabroncs cseréje'],
        ['id' => 7, 'name' => 'Műszaki vizsga előkészítés', 'duration_minutes' => 90, 'description' => 'Teljes átvizsgálás műszaki vizsgához'],
        ['id' => 8, 'name' => 'Diagnosztika', 'duration_minutes' => 60, 'description' => 'Elektronikus diagnosztika'],
        ['id' => 9, 'name' => 'Akkumulátor csere', 'duration_minutes' => 20, 'description' => 'Akkumulátor csere és ellenőrzés'],
        ['id' => 10, 'name' => 'Szélvédő csere', 'duration_minutes' => 120, 'description' => 'Szélvédő üveg csere']
    ];
}

// User adatok
$user = [
    'name' => get_current_user_name(),
    'email' => $_SESSION['user_email'] ?? ''
];

// AJAX kérés a foglalt időpontokhoz
if (isset($_GET['ajax']) && $_GET['ajax'] === 'get_booked_times' && isset($_GET['date'])) {
    header('Content-Type: application/json');
    $date = $_GET['date'];
    $ajax_service_id = isset($_GET['service_id']) ? (int)$_GET['service_id'] : $service_id;
    
    $stmt = $pdo->prepare("
        SELECT booking_time, estimated_duration 
        FROM bookings 
        WHERE service_id = ? 
        AND booking_date = ? 
        AND status != 'cancelled'
    ");
    $stmt->execute([$ajax_service_id, $date]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Számoljuk ki, mely időpontok foglaltak az időtartamok alapján
    $blocked_times = [];
    
    foreach ($bookings as $booking) {
        $start_time = strtotime($booking['booking_time']);
        $duration = $booking['estimated_duration'] ?? 60; // alapértelmezett 60 perc
        
        // Kezdő időpont hozzáadása
        $blocked_times[] = date('H:i', $start_time);
        
        // Minden óra hozzáadása az időtartam alatt
        $current_time = $start_time;
        $end_time = $start_time + ($duration * 60); // percből másodperc
        
        while ($current_time < $end_time) {
            $current_time += 3600; // +1 óra
            if ($current_time < $end_time) {
                $blocked_times[] = date('H:i', $current_time);
            }
        }
    }
    
    // Egyedi időpontok
    $blocked_times = array_unique($blocked_times);
    
    echo json_encode(['blocked_times' => $blocked_times, 'bookings' => $bookings]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_date = trim($_POST['booking_date'] ?? '');
    $booking_time = trim($_POST['booking_time'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $message = trim($_POST['message'] ?? '');
    $selected_services = $_POST['services'] ?? [];
    
    if (empty($booking_date) || empty($booking_time) || empty($name) || empty($phone) || empty($email)) {
        $error = 'Kérjük töltse ki az összes kötelező mezőt!';
    } elseif (empty($selected_services)) {
        $error = 'Kérjük válasszon legalább egy szolgáltatást!';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Érvénytelen email cím!';
    } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
        $error = 'Csak jövőbeli időpontot lehet foglalni!';
    } else {
        // Becsült időtartam számítása
        $total_duration = 0;
        $service_names = [];
        
        // Alapértelmezett szolgáltatások (ha a tábla nem létezik)
        $default_services = [
            1 => ['name' => 'Olajcsere', 'duration_minutes' => 30],
            2 => ['name' => 'Fékjavítás', 'duration_minutes' => 90],
            3 => ['name' => 'Futómű javítás', 'duration_minutes' => 120],
            4 => ['name' => 'Motorjavítás', 'duration_minutes' => 240],
            5 => ['name' => 'Klíma szerviz', 'duration_minutes' => 60],
            6 => ['name' => 'Gumiabroncs csere', 'duration_minutes' => 45],
            7 => ['name' => 'Műszaki vizsga előkészítés', 'duration_minutes' => 90],
            8 => ['name' => 'Diagnosztika', 'duration_minutes' => 60],
            9 => ['name' => 'Akkumulátor csere', 'duration_minutes' => 20],
            10 => ['name' => 'Szélvédő csere', 'duration_minutes' => 120]
        ];
        
        foreach ($selected_services as $service_type_id) {
            try {
                $stmt = $pdo->prepare("SELECT name, duration_minutes FROM service_types WHERE id = ?");
                $stmt->execute([$service_type_id]);
                $st = $stmt->fetch();
                if ($st) {
                    $total_duration += $st['duration_minutes'];
                    $service_names[] = $st['name'];
                }
            } catch (PDOException $e) {
                // Ha a tábla nem létezik, használjuk az alapértelmezett értékeket
                if (isset($default_services[$service_type_id])) {
                    $total_duration += $default_services[$service_type_id]['duration_minutes'];
                    $service_names[] = $default_services[$service_type_id]['name'];
                }
            }
        }
        
        // Ellenőrizzük, hogy az időpont még szabad-e
        $stmt = $pdo->prepare("
            SELECT COUNT(*) FROM bookings 
            WHERE service_id = ? 
            AND booking_date = ? 
            AND booking_time = ? 
            AND status != 'cancelled'
        ");
        $stmt->execute([$service_id, $booking_date, $booking_time]);
        $count = $stmt->fetchColumn();
        
        if ($count > 0) {
            $error = 'Ez az időpont már foglalt! Kérjük válasszon másik időpontot.';
        } else {
            // Foglalás létrehozása
            $services_json = json_encode($service_names);
            
            $stmt = $pdo->prepare("
                INSERT INTO bookings (user_id, service_id, booking_date, booking_time, name, phone, email, message, services_requested, estimated_duration, status)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
            ");
            
            if ($stmt->execute([get_current_user_id(), $service_id, $booking_date, $booking_time, $name, $phone, $email, $message, $services_json, $total_duration])) {
                $success = 'Időpont foglalás sikeresen elküldve! A szerviz hamarosan visszaigazolja.';
            } else {
                $error = 'Hiba történt a foglalás során!';
            }
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar-check"></i> Időpont Foglalás
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Szerviz információk -->
                    <div class="alert alert-info">
                        <h5 class="mb-2">
                            <i class="bi bi-wrench"></i> <?php echo htmlspecialchars($service['name']); ?>
                        </h5>
                        <p class="mb-1">
                            <i class="bi bi-geo-alt"></i> 
                            <?php echo htmlspecialchars($service['city']); ?>, 
                            <?php echo htmlspecialchars($service['address']); ?>
                        </p>
                        <p class="mb-0">
                            <i class="bi bi-telephone"></i> 
                            <?php echo htmlspecialchars($service['phone']); ?>
                        </p>
                    </div>
                    
                    <?php if ($error): ?>
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($success): ?>
                        <div class="alert alert-success">
                            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                            <hr>
                            <a href="profile.php" class="btn btn-success btn-sm">
                                <i class="bi bi-person-circle"></i> Foglalásaim megtekintése
                            </a>
                            <a href="service_view.php?id=<?php echo $service_id; ?>" class="btn btn-secondary btn-sm">
                                <i class="bi bi-arrow-left"></i> Vissza a szervizhez
                            </a>
                        </div>
                    <?php else: ?>
                        <form method="POST" action="" id="bookingForm">
                            <!-- Szolgáltatások kiválasztása -->
                            <div class="mb-4">
                                <label class="form-label">
                                    <i class="bi bi-tools"></i> Kért Szolgáltatások <span class="text-danger">*</span>
                                </label>
                                <div class="row">
                                    <?php foreach ($service_types as $st): ?>
                                        <div class="col-md-6 mb-2">
                                            <div class="form-check">
                                                <input class="form-check-input service-checkbox" type="checkbox" 
                                                       name="services[]" value="<?php echo $st['id']; ?>" 
                                                       id="service_<?php echo $st['id']; ?>"
                                                       data-duration="<?php echo $st['duration_minutes']; ?>"
                                                       data-name="<?php echo htmlspecialchars($st['name']); ?>">
                                                <label class="form-check-label" for="service_<?php echo $st['id']; ?>">
                                                    <strong><?php echo htmlspecialchars($st['name']); ?></strong>
                                                    <small class="text-muted">(~<?php echo $st['duration_minutes']; ?> perc)</small>
                                                    <br>
                                                    <small><?php echo htmlspecialchars($st['description']); ?></small>
                                                </label>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Becsült időtartam -->
                                <div class="alert alert-success mt-3" id="durationAlert" style="display: none;">
                                    <i class="bi bi-clock"></i> 
                                    <strong>Becsült időtartam:</strong> 
                                    <span id="totalDuration">0</span> perc 
                                    (<span id="totalHours">0</span> óra)
                                    <br>
                                    <small>A jármű várhatóan <strong><span id="estimatedTime"></span></strong>-kor lesz kész.</small>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="booking_date" class="form-label">
                                        Dátum <span class="text-danger">*</span>
                                    </label>
                                    <input type="date" class="form-control" id="booking_date" name="booking_date" 
                                           min="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="booking_time" class="form-label">
                                        Időpont <span class="text-danger">*</span>
                                    </label>
                                    <select class="form-select" id="booking_time" name="booking_time" required>
                                        <option value="">Válasszon dátumot először</option>
                                    </select>
                                    <small class="text-muted">
                                        <i class="bi bi-info-circle"></i> A foglalt időpontok le vannak tiltva
                                    </small>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="name" class="form-label">
                                    Név <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="phone" class="form-label">
                                        Telefonszám <span class="text-danger">*</span>
                                    </label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           placeholder="+36 30 123 4567" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="email" class="form-label">
                                        Email cím <span class="text-danger">*</span>
                                    </label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="message" class="form-label">
                                    Megjegyzés (opcionális)
                                </label>
                                <textarea class="form-control" id="message" name="message" rows="3" 
                                          placeholder="További információk a munkálatokról..."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary btn-lg">
                                    <i class="bi bi-calendar-check"></i> Időpont foglalása
                                </button>
                                <a href="service_view.php?id=<?php echo $service_id; ?>" class="btn btn-secondary">
                                    <i class="bi bi-arrow-left"></i> Vissza
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Időtartam számítás
function updateDuration() {
    let totalMinutes = 0;
    let selectedServices = [];
    
    document.querySelectorAll('.service-checkbox:checked').forEach(checkbox => {
        totalMinutes += parseInt(checkbox.dataset.duration);
        selectedServices.push(checkbox.dataset.name);
    });
    
    if (totalMinutes > 0) {
        const hours = Math.floor(totalMinutes / 60);
        const minutes = totalMinutes % 60;
        const hoursText = hours > 0 ? hours + ' óra ' + (minutes > 0 ? minutes + ' perc' : '') : minutes + ' perc';
        
        document.getElementById('totalDuration').textContent = totalMinutes;
        document.getElementById('totalHours').textContent = hoursText;
        document.getElementById('durationAlert').style.display = 'block';
        
        // Becsült befejezési idő
        const bookingTime = document.getElementById('booking_time').value;
        if (bookingTime) {
            const [hours, mins] = bookingTime.split(':');
            const startTime = new Date();
            startTime.setHours(parseInt(hours), parseInt(mins), 0);
            startTime.setMinutes(startTime.getMinutes() + totalMinutes);
            
            const endHours = String(startTime.getHours()).padStart(2, '0');
            const endMins = String(startTime.getMinutes()).padStart(2, '0');
            document.getElementById('estimatedTime').textContent = endHours + ':' + endMins;
        }
    } else {
        document.getElementById('durationAlert').style.display = 'none';
    }
}

// Foglalt időpontok lekérése
async function loadAvailableTimes(date) {
    if (!date) {
        console.log('Nincs dátum kiválasztva');
        return;
    }
    
    console.log('Időpontok betöltése:', date);
    
    try {
        const url = `booking_create.php?ajax=get_booked_times&date=${date}&service_id=<?php echo $service_id; ?>`;
        console.log('AJAX URL:', url);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error('Hálózati hiba: ' + response.status);
        }
        
        const data = await response.json();
        console.log('Foglalt időpontok:', data);
        
        const blockedTimes = data.blocked_times || [];
        const bookings = data.bookings || [];
        
        const timeSelect = document.getElementById('booking_time');
        timeSelect.innerHTML = '<option value="">Válasszon időpontot</option>';
        
        // Összes lehetséges időpont
        const allTimes = ['08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00'];
        
        allTimes.forEach(time => {
            const option = document.createElement('option');
            option.value = time;
            
            // Ellenőrizzük, hogy foglalt-e (beleértve az időtartamot is)
            const isBlocked = blockedTimes.includes(time);
            
            if (isBlocked) {
                // Keressük meg, melyik foglalás miatt van blokkolva
                let reason = '';
                bookings.forEach(booking => {
                    const startTime = booking.booking_time.substring(0, 5);
                    const duration = booking.estimated_duration || 60;
                    const startMinutes = parseInt(startTime.split(':')[0]) * 60 + parseInt(startTime.split(':')[1]);
                    const endMinutes = startMinutes + duration;
                    const currentMinutes = parseInt(time.split(':')[0]) * 60 + parseInt(time.split(':')[1]);
                    
                    if (currentMinutes >= startMinutes && currentMinutes < endMinutes) {
                        const hours = Math.floor(duration / 60);
                        const mins = duration % 60;
                        const durationText = hours > 0 ? `${hours}ó ${mins}p` : `${mins}p`;
                        reason = ` (${startTime}-tól ${durationText})`;
                    }
                });
                
                option.textContent = time + ' - Foglalt ❌' + reason;
                option.disabled = true;
                option.style.color = '#dc3545';
            } else {
                option.textContent = time + ' - Szabad ✓';
            }
            
            timeSelect.appendChild(option);
        });
        
        console.log('Időpontok sikeresen betöltve');
    } catch (error) {
        console.error('Hiba az időpontok betöltésekor:', error);
        alert('Hiba történt az időpontok betöltésekor. Kérjük próbálja újra!');
    }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM betöltve, event listener-ek beállítása');
    
    // Szolgáltatás checkbox-ok
    document.querySelectorAll('.service-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateDuration);
    });

    // Dátum választó
    const dateInput = document.getElementById('booking_date');
    if (dateInput) {
        dateInput.addEventListener('change', function() {
            console.log('Dátum megváltoztatva:', this.value);
            loadAvailableTimes(this.value);
        });
    } else {
        console.error('booking_date elem nem található!');
    }

    // Időpont választó
    const timeInput = document.getElementById('booking_time');
    if (timeInput) {
        timeInput.addEventListener('change', updateDuration);
    }
});
</script>

<?php include 'includes/footer.php'; ?>
