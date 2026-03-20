-- alapszerviz.hu Adatbázis Létrehozó Script - PRODUCTION
-- MySQL/MariaDB

-- Adatbázis létrehozása (ha még nem létezik)
CREATE DATABASE IF NOT EXISTS rh57507_alapszerviz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE rh57507_alapszerviz;

-- Users tábla
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    is_admin TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_is_admin (is_admin)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Services tábla
CREATE TABLE IF NOT EXISTS services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    description TEXT,
    phone VARCHAR(20) NOT NULL,
    hours VARCHAR(255),
    image VARCHAR(255),
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    is_premium TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_city (city),
    INDEX idx_user_id (user_id),
    INDEX idx_is_premium (is_premium)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments tábla
CREATE TABLE IF NOT EXISTS comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_service_id (service_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support tábla
CREATE TABLE IF NOT EXISTS support (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT,
    message TEXT NOT NULL,
    from_admin TINYINT(1) DEFAULT 0,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Ratings tábla
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_rating (service_id, user_id),
    INDEX idx_service_id (service_id),
    INDEX idx_user_id (user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Favorites tábla
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, service_id),
    INDEX idx_user_id (user_id),
    INDEX idx_service_id (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Bookings tábla
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

-- Service types tábla
CREATE TABLE IF NOT EXISTS service_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration_minutes INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- View a services táblához (átlagos értékelés és értékelések száma)
CREATE OR REPLACE VIEW services_with_ratings AS
SELECT 
    s.*,
    COALESCE(AVG(r.rating), 0) as average_rating,
    COUNT(r.id) as rating_count
FROM services s
LEFT JOIN ratings r ON s.id = r.service_id
GROUP BY s.id;

-- Admin user létrehozása
-- Jelszó: admin123
INSERT INTO users (name, email, password, is_admin) VALUES 
('Admin', 'admin@alapszerviz.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1)
ON DUPLICATE KEY UPDATE name=name;

-- Teszt user létrehozása
-- Jelszó: user123
INSERT INTO users (name, email, password, is_admin) VALUES 
('Teszt User', 'user@alapszerviz.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0)
ON DUPLICATE KEY UPDATE name=name;

-- Alapértelmezett szolgáltatás típusok
INSERT INTO service_types (id, name, duration_minutes, description) VALUES
(1, 'Olajcsere', 30, 'Motor olaj és olajszűrő csere'),
(2, 'Fékjavítás', 90, 'Fékbetét, féktárcsa csere és beállítás'),
(3, 'Futómű javítás', 120, 'Futómű alkatrészek cseréje és beállítás'),
(4, 'Motorjavítás', 240, 'Motor diagnosztika és javítás'),
(5, 'Klíma szerviz', 60, 'Klíma tisztítás, töltés'),
(6, 'Gumiabroncs csere', 45, 'Négy kerék gumiabroncs cseréje'),
(7, 'Műszaki vizsga előkészítés', 90, 'Teljes átvizsgálás műszaki vizsgához'),
(8, 'Diagnosztika', 60, 'Elektronikus diagnosztika'),
(9, 'Akkumulátor csere', 20, 'Akkumulátor csere és ellenőrzés'),
(10, 'Szélvédő csere', 120, 'Szélvédő üveg csere')
ON DUPLICATE KEY UPDATE name=name;
