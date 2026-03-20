-- Összes új funkció telepítése egyszerre
-- Futtasd ezt ha már létező adatbázisod van és minden új funkciót szeretnél

USE alapszerviz;

-- 1. Support status mező
ALTER TABLE support 
ADD COLUMN IF NOT EXISTS status ENUM('open', 'closed') DEFAULT 'open' AFTER from_admin,
ADD INDEX IF NOT EXISTS idx_status (status);

UPDATE support SET status = 'open' WHERE status IS NULL;

-- 2. Prémium és statisztika mezők
ALTER TABLE services 
ADD COLUMN IF NOT EXISTS is_premium TINYINT(1) DEFAULT 0 AFTER status,
ADD COLUMN IF NOT EXISTS views INT DEFAULT 0 AFTER is_premium,
ADD INDEX IF NOT EXISTS idx_is_premium (is_premium);

UPDATE services SET is_premium = 0 WHERE is_premium IS NULL;
UPDATE services SET views = 0 WHERE views IS NULL;

-- 3. Értékelési rendszer
CREATE TABLE IF NOT EXISTS ratings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_service (user_id, service_id),
    INDEX idx_service_id (service_id),
    INDEX idx_rating (rating)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

ALTER TABLE services 
ADD COLUMN IF NOT EXISTS average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER views,
ADD COLUMN IF NOT EXISTS rating_count INT DEFAULT 0 AFTER average_rating,
ADD INDEX IF NOT EXISTS idx_average_rating (average_rating);

-- 4. Kedvencek rendszer
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    service_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (service_id) REFERENCES services(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_service (user_id, service_id),
    INDEX idx_user_id (user_id),
    INDEX idx_service_id (service_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SELECT 'Összes új funkció sikeresen telepítve!' as message;
SELECT 'Support status, Prémium, Statisztikák, Értékelések, Kedvencek - KÉSZ!' as features;
