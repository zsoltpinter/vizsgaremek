-- alapszerviz.hu Adatbázis Létrehozó Script
-- MySQL/MariaDB

-- Adatbázis létrehozása (ha még nem létezik)
CREATE DATABASE IF NOT EXISTS alapszerviz CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE alapszerviz;

-- Users tábla
CREATE TABLE users (
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
CREATE TABLE services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    city VARCHAR(100) NOT NULL,
    address VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    phone VARCHAR(20) NOT NULL,
    hours VARCHAR(255) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    status ENUM('pending', 'approved') DEFAULT 'pending',
    is_premium TINYINT(1) DEFAULT 0,
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_user_id (user_id),
    INDEX idx_is_premium (is_premium),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Comments tábla
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_service_id (service_id),
    INDEX idx_user_id (user_id),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Support tábla
CREATE TABLE support (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    admin_id INT DEFAULT NULL,
    message TEXT NOT NULL,
    from_admin TINYINT(1) DEFAULT 0,
    status ENUM('open', 'closed') DEFAULT 'open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_from_admin (from_admin),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Első admin user létrehozása
-- Jelszó: admin123 (FONTOS: Változtasd meg éles környezetben!)
INSERT INTO users (name, email, password, is_admin) 
VALUES ('Admin', 'admin@alapszerviz.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 1);

-- Teszt user létrehozása (opcionális)
-- Jelszó: user123
INSERT INTO users (name, email, password, is_admin) 
VALUES ('Teszt Felhasználó', 'user@alapszerviz.hu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 0);

-- Minta szervizek (opcionális)
INSERT INTO services (user_id, name, city, address, description, phone, hours, status) VALUES
(2, 'AutoPro Szerviz', 'Budapest', '1117 Budapest, Irinyi József utca 4-20', 'Teljes körű autószerviz szolgáltatás, klímaszerelés, futómű beállítás, fékjavítás. Gyors és megbízható munka.', '+36 1 234 5678', 'H-P: 8:00-18:00, Szo: 9:00-13:00', 'approved'),
(2, 'Gyorsszerviz Kft', 'Debrecen', '4031 Debrecen, Balmazújvárosi út 1', 'Gyors olajcsere, szerviz, műszaki vizsgáztatás. Minden autómárkára vállalunk munkát.', '+36 52 123 456', 'H-P: 7:30-17:00', 'approved'),
(2, 'Profi Autóház', 'Szeged', '6724 Szeged, Dorozsmai út 5', 'Márkafüggetlen szerviz, diagnosztika, motorjavítás, karosszéria munka.', '+36 62 987 654', 'H-P: 8:00-17:00, Szo: 8:00-12:00', 'pending');
