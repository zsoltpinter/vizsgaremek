-- Értékelési rendszer hozzáadása
-- Futtasd ezt ha már létező adatbázisod van

USE alapszerviz;

-- Ratings tábla létrehozása
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

-- Átlagos értékelés mező hozzáadása a services táblához
ALTER TABLE services 
ADD COLUMN average_rating DECIMAL(3,2) DEFAULT 0.00 AFTER views,
ADD COLUMN rating_count INT DEFAULT 0 AFTER average_rating;

-- Indexek
ALTER TABLE services 
ADD INDEX idx_average_rating (average_rating);

SELECT 'Értékelési rendszer sikeresen telepítve!' as message;
