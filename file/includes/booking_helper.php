<?php
/**
 * Foglalás Kezelés és Validáció
 */

require_once __DIR__ . '/db.php';

/**
 * Ellenőrzi, hogy az időpont foglalható-e (nincs ütközés)
 */
function validate_booking_time($service_id, $date, $time, $duration) {
    $pdo = getDB();
    
    // Ellenőrizzük, hogy a dátum nem múltbeli-e
    $booking_datetime = strtotime("$date $time");
    if ($booking_datetime < time()) {
        return ['valid' => false, 'error' => 'Múltbeli időpontra nem lehet foglalni!'];
    }
    
    // Realisztikus időtartam ellenőrzés
    $realistic_check = is_booking_time_realistic($time, $duration);
    if (!$realistic_check['valid']) {
        return $realistic_check;
    }
    
    // Ütközés ellenőrzés
    $end_time = date('H:i:s', strtotime($time) + ($duration * 60));
    
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as count 
        FROM bookings 
        WHERE service_id = ? 
        AND booking_date = ? 
        AND status IN ('pending', 'confirmed')
        AND (
            (booking_time <= ? AND DATE_ADD(booking_time, INTERVAL estimated_duration MINUTE) > ?) OR
            (booking_time < ? AND DATE_ADD(booking_time, INTERVAL estimated_duration MINUTE) >= ?)
        )
    ");
    $stmt->execute([$service_id, $date, $time, $time, $end_time, $end_time]);
    $conflict = $stmt->fetch()['count'];
    
    if ($conflict > 0) {
        return ['valid' => false, 'error' => 'Ez az időpont már foglalt! Válassz másik időpontot.'];
    }
    
    return ['valid' => true];
}

/**
 * Realisztikus időtartam ellenőrzés
 * Pl. 10 órás szerviz nem fér bele az utolsó időpontba
 */
function is_booking_time_realistic($time, $duration) {
    // Munkaidő: 8:00 - 18:00 (10 óra)
    $work_start = strtotime('08:00:00');
    $work_end = strtotime('18:00:00');
    $booking_start = strtotime($time);
    $booking_end = $booking_start + ($duration * 60);
    
    // Ellenőrizzük, hogy a foglalás kezdete munkaidőben van-e
    if ($booking_start < $work_start || $booking_start >= $work_end) {
        return ['valid' => false, 'error' => 'A foglalás csak 8:00 és 18:00 között lehetséges!'];
    }
    
    // Ellenőrizzük, hogy a foglalás vége belefér-e a munkaidőbe
    if ($booking_end > $work_end) {
        $max_duration = ($work_end - $booking_start) / 60;
        return [
            'valid' => false, 
            'error' => "Ez az időpont túl késői ehhez az időtartamhoz! Maximum {$max_duration} perc lehetséges erre az időpontra."
        ];
    }
    
    // Maximum 8 órás (480 perc) szerviz
    if ($duration > 480) {
        return ['valid' => false, 'error' => 'Maximum 8 órás szerviz foglalható!'];
    }
    
    return ['valid' => true];
}

/**
 * Elérhető időpontok lekérése egy adott napra
 */
function get_available_slots($service_id, $date) {
    $pdo = getDB();
    
    // Munkaidő: 8:00 - 18:00, 30 perces slotok
    $slots = [];
    $start = strtotime('08:00:00');
    $end = strtotime('18:00:00');
    
    for ($time = $start; $time < $end; $time += 1800) { // 30 perc = 1800 másodperc
        $time_str = date('H:i:s', $time);
        
        // Ellenőrizzük, hogy ez az időpont foglalt-e
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM bookings 
            WHERE service_id = ? 
            AND booking_date = ? 
            AND booking_time <= ?
            AND DATE_ADD(booking_time, INTERVAL estimated_duration MINUTE) > ?
            AND status IN ('pending', 'confirmed')
        ");
        $stmt->execute([$service_id, $date, $time_str, $time_str]);
        $is_booked = $stmt->fetch()['count'] > 0;
        
        $slots[] = [
            'time' => $time_str,
            'display' => date('H:i', $time),
            'available' => !$is_booked
        ];
    }
    
    return $slots;
}

/**
 * Foglalás létrehozása
 */
function create_booking($user_id, $service_id, $booking_data) {
    $pdo = getDB();
    
    // Validáció
    $validation = validate_booking_time(
        $service_id, 
        $booking_data['booking_date'], 
        $booking_data['booking_time'], 
        $booking_data['estimated_duration']
    );
    
    if (!$validation['valid']) {
        return ['success' => false, 'error' => $validation['error']];
    }
    
    // Foglalás létrehozása
    try {
        $stmt = $pdo->prepare("
            INSERT INTO bookings (user_id, service_id, booking_date, booking_time, name, phone, email, services_requested, estimated_duration, message, status) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')
        ");
        $stmt->execute([
            $user_id,
            $service_id,
            $booking_data['booking_date'],
            $booking_data['booking_time'],
            $booking_data['customer_name'],
            $booking_data['customer_phone'],
            $booking_data['customer_email'] ?? '',
            $booking_data['service_type'],
            $booking_data['estimated_duration'],
            $booking_data['notes'] ?? null
        ]);
        
        return ['success' => true, 'booking_id' => $pdo->lastInsertId()];
    } catch (Exception $e) {
        error_log("Booking creation error: " . $e->getMessage());
        return ['success' => false, 'error' => 'Hiba történt a foglalás létrehozása során! Kérlek, próbáld újra.'];
    }
}

/**
 * Foglalás lemondása
 */
function cancel_booking($booking_id, $user_id) {
    $pdo = getDB();
    
    // Ellenőrizzük, hogy a user-é a foglalás
    $stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ? AND user_id = ?");
    $stmt->execute([$booking_id, $user_id]);
    $booking = $stmt->fetch();
    
    if (!$booking) {
        return ['success' => false, 'error' => 'Nem található ilyen foglalás!'];
    }
    
    // Lemondás
    $stmt = $pdo->prepare("UPDATE bookings SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$booking_id]);
    
    return ['success' => true];
}

/**
 * User foglalásainak lekérése
 */
function get_user_bookings($user_id) {
    $pdo = getDB();
    
    $stmt = $pdo->prepare("
        SELECT b.*, s.name as service_name, s.city, s.address, s.phone 
        FROM bookings b 
        JOIN services s ON b.service_id = s.id 
        WHERE b.user_id = ? 
        ORDER BY b.booking_date DESC, b.booking_time DESC
    ");
    $stmt->execute([$user_id]);
    
    return $stmt->fetchAll();
}
