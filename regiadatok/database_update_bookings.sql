-- Időpontfoglalás tábla létrehozása
CREATE TABLE IF NOT EXISTS bookings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    booking_date DATE NOT NULL,
    booking_time TIME NOT NULL,
    name VARCHAR(100) NOT NULL,
    phone VARCHAR(20) NOT NULL,
    email VARCHAR(100) NOT NULL,
    message TEXT,
    services_requested TEXT,
    estimated_duration INT DEFAULT 60,
    status ENUM('pending', 'confirmed', 'cancelled', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_service_id (service_id),
    INDEX idx_booking_date (booking_date),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Szolgáltatás típusok tábla
CREATE TABLE IF NOT EXISTS service_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration_minutes INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alapértelmezett szolgáltatás típusok
INSERT INTO service_types (name, duration_minutes, description) VALUES
('Olajcsere', 30, 'Motor olaj és olajszűrő csere'),
('Fékjavítás', 90, 'Fékbetét, féktárcsa csere és beállítás'),
('Futómű javítás', 120, 'Futómű alkatrészek cseréje és beállítás'),
('Motorjavítás', 240, 'Motor diagnosztika és javítás'),
('Klíma szerviz', 60, 'Klíma tisztítás, töltés'),
('Gumiabroncs csere', 45, 'Négy kerék gumiabroncs cseréje'),
('Műszaki vizsga előkészítés', 90, 'Teljes átvizsgálás műszaki vizsgához'),
('Diagnosztika', 60, 'Elektronikus diagnosztika'),
('Akkumulátor csere', 20, 'Akkumulátor csere és ellenőrzés'),
('Szélvédő csere', 120, 'Szélvédő üveg csere');
