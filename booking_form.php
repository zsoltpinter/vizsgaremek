<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';
require_once 'includes/booking_helper.php';

require_login();

$service_id = $_GET['service_id'] ?? 0;
$pdo = getDB();

// Szerviz lekérdezése
$stmt = $pdo->prepare("SELECT * FROM services WHERE id = ?");
$stmt->execute([$service_id]);
$service = $stmt->fetch();

if (!$service) {
    $_SESSION['error'] = 'Nem található ilyen szerviz!';
    header('Location: services.php');
    exit;
}

include 'includes/header.php';
?>

<div class="container mt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="card-header bg-success text-white">
                    <h4 class="mb-0">
                        <i class="bi bi-calendar-check"></i> Időpont foglalása
                    </h4>
                </div>
                <div class="card-body">
                    <!-- Szerviz info -->
                    <div class="alert alert-info">
                        <h5><i class="bi bi-shop"></i> <?php echo htmlspecialchars($service['name']); ?></h5>
                        <p class="mb-0">
                            <i class="bi bi-geo-alt"></i> <?php echo htmlspecialchars($service['city']); ?>, <?php echo htmlspecialchars($service['address']); ?><br>
                            <i class="bi bi-telephone"></i> <?php echo htmlspecialchars($service['phone']); ?>
                        </p>
                    </div>

                    <?php if (isset($_SESSION['booking_error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show">
                            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['booking_error']); ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                        <?php unset($_SESSION['booking_error']); ?>
                    <?php endif; ?>

                    <!-- Foglalási form -->
                    <form method="POST" action="booking_create.php">
                        <input type="hidden" name="service_id" value="<?php echo $service_id; ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="customer_name" class="form-label">
                                    <i class="bi bi-person"></i> Név <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                       placeholder="Add meg a neved" autocomplete="name" required>
                                <small class="text-muted">A foglaláshoz szükséges</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="customer_phone" class="form-label">
                                    <i class="bi bi-telephone"></i> Telefonszám <span class="text-danger">*</span>
                                </label>
                                <input type="tel" class="form-control" id="customer_phone" name="customer_phone" 
                                       placeholder="+36 XX XXX XXXX" autocomplete="tel" required>
                                <small class="text-muted">Visszaigazoláshoz szükséges</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="booking_date" class="form-label">
                                    <i class="bi bi-calendar3"></i> Dátum <span class="text-danger">*</span>
                                </label>
                                <input type="date" class="form-control" id="booking_date" name="booking_date" 
                                       min="<?php echo date('Y-m-d'); ?>" autocomplete="off" required>
                                <small class="text-muted">Válassz egy jövőbeli dátumot</small>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="booking_time" class="form-label">
                                    <i class="bi bi-clock"></i> Időpont <span class="text-danger">*</span>
                                </label>
                                <select class="form-select" id="booking_time" name="booking_time" autocomplete="off" required>
                                    <option value="">Válassz időpontot...</option>
                                    <?php
                                    // Generate time slots from 8:00 to 17:30 (30 min intervals)
                                    for ($hour = 8; $hour < 18; $hour++) {
                                        for ($min = 0; $min < 60; $min += 30) {
                                            $time = sprintf('%02d:%02d:00', $hour, $min);
                                            $display = sprintf('%02d:%02d', $hour, $min);
                                            echo "<option value=\"{$time}\">{$display}</option>";
                                        }
                                    }
                                    ?>
                                </select>
                                <small class="text-muted">Munkaidő: 8:00 - 18:00</small>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label">
                                    <i class="bi bi-tools"></i> Szerviz típusa(i) <span class="text-danger">*</span>
                                </label>
                                <div class="border rounded p-3" style="max-height: 300px; overflow-y: auto;">
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Olajcsere" data-duration="60" id="service_1">
                                        <label class="form-check-label" for="service_1">
                                            Olajcsere - 1 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Fékjavítás" data-duration="120" id="service_2">
                                        <label class="form-check-label" for="service_2">
                                            Fékjavítás - 2 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Gumiabroncs csere" data-duration="60" id="service_3">
                                        <label class="form-check-label" for="service_3">
                                            Gumiabroncs csere - 1 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Műszaki vizsga" data-duration="90" id="service_4">
                                        <label class="form-check-label" for="service_4">
                                            Műszaki vizsga - 1.5 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Klíma szerviz" data-duration="90" id="service_5">
                                        <label class="form-check-label" for="service_5">
                                            Klíma szerviz - 1.5 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Futómű beállítás" data-duration="120" id="service_6">
                                        <label class="form-check-label" for="service_6">
                                            Futómű beállítás - 2 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Motor diagnosztika" data-duration="90" id="service_7">
                                        <label class="form-check-label" for="service_7">
                                            Motor diagnosztika - 1.5 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Akkumulátor csere" data-duration="30" id="service_8">
                                        <label class="form-check-label" for="service_8">
                                            Akkumulátor csere - 30 perc
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Szélvédő csere" data-duration="180" id="service_9">
                                        <label class="form-check-label" for="service_9">
                                            Szélvédő csere - 3 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Komplett szerviz" data-duration="240" id="service_10">
                                        <label class="form-check-label" for="service_10">
                                            Komplett szerviz - 4 óra
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input service-checkbox" type="checkbox" value="Egyéb" data-duration="60" id="service_11">
                                        <label class="form-check-label" for="service_11">
                                            Egyéb - 1 óra
                                        </label>
                                    </div>
                                </div>
                                <input type="hidden" id="service_type" name="service_type" required>
                                <small class="text-muted">Válassz egy vagy több szolgáltatást (maximum 8 óra összesen)</small>
                                <div id="selected_services" class="mt-2"></div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label for="estimated_duration" class="form-label">
                                    <i class="bi bi-hourglass-split"></i> Becsült időtartam összesen <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control" id="estimated_duration_display" readonly value="0 perc">
                                <input type="hidden" id="estimated_duration" name="estimated_duration" required>
                                <small class="text-muted">Automatikusan számolva a kiválasztott szolgáltatások alapján</small>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notes" class="form-label">
                                <i class="bi bi-chat-left-text"></i> Megjegyzés (opcionális)
                            </label>
                            <textarea class="form-control" id="notes" name="notes" rows="3" 
                                      placeholder="Írj ide bármilyen további információt..." autocomplete="off"></textarea>
                        </div>
                        
                        <div class="alert alert-warning">
                            <i class="bi bi-info-circle"></i> 
                            <strong>Fontos:</strong> A foglalás létrehozása után a szerviz felveszi veled a kapcsolatot a részletek egyeztetéséhez.
                        </div>

                        <div class="d-flex gap-2">
                            <a href="service_view.php?id=<?php echo $service_id; ?>" class="btn btn-secondary">
                                <i class="bi bi-arrow-left"></i> Vissza
                            </a>
                            <button type="submit" class="btn btn-success flex-grow-1">
                                <i class="bi bi-check-circle"></i> Foglalás létrehozása
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Több szolgáltatás kiválasztása és automatikus időtartam számítás
const checkboxes = document.querySelectorAll('.service-checkbox');
const serviceTypeInput = document.getElementById('service_type');
const durationInput = document.getElementById('estimated_duration');
const durationDisplay = document.getElementById('estimated_duration_display');
const selectedServicesDiv = document.getElementById('selected_services');

function updateServices() {
    const selected = [];
    let totalDuration = 0;
    
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            selected.push(checkbox.value);
            totalDuration += parseInt(checkbox.getAttribute('data-duration'));
        }
    });
    
    // Maximum 8 óra (480 perc) ellenőrzés
    if (totalDuration > 480) {
        alert('A kiválasztott szolgáltatások összesen több mint 8 órát vennének igénybe! Kérlek, válassz kevesebb szolgáltatást.');
        event.target.checked = false;
        updateServices(); // Újraszámolás
        return;
    }
    
    // Szolgáltatások megjelenítése
    if (selected.length > 0) {
        serviceTypeInput.value = selected.join(', ');
        durationInput.value = totalDuration;
        
        const hours = Math.floor(totalDuration / 60);
        const minutes = totalDuration % 60;
        let displayText = '';
        
        if (hours > 0) {
            displayText += hours + ' óra';
        }
        if (minutes > 0) {
            if (hours > 0) displayText += ' ';
            displayText += minutes + ' perc';
        }
        if (totalDuration === 0) {
            displayText = '0 perc';
        }
        
        durationDisplay.value = displayText + ' (' + totalDuration + ' perc)';
        
        selectedServicesDiv.innerHTML = '<div class="alert alert-success mt-2"><strong>Kiválasztva:</strong> ' + selected.join(', ') + '</div>';
    } else {
        serviceTypeInput.value = '';
        durationInput.value = '';
        durationDisplay.value = '0 perc';
        selectedServicesDiv.innerHTML = '';
    }
}

checkboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateServices);
});

// Form validáció
document.querySelector('form').addEventListener('submit', function(e) {
    if (!serviceTypeInput.value) {
        e.preventDefault();
        alert('Kérlek, válassz legalább egy szolgáltatást!');
        return false;
    }
});
</script>

<?php include 'includes/footer.php'; ?>
