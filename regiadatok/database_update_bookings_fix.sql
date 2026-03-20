-- Ellenőrizzük és hozzáadjuk a hiányzó oszlopokat a bookings táblához
ALTER TABLE bookings 
ADD COLUMN IF NOT EXISTS services_requested TEXT AFTER message,
ADD COLUMN IF NOT EXISTS estimated_duration INT DEFAULT 60 AFTER services_requested;

-- Szolgáltatás típusok tábla létrehozása (ha még nem létezik)
CREATE TABLE IF NOT EXISTS service_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    duration_minutes INT NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Alapértelmezett szolgáltatás típusok (csak ha a tábla üres)
INSERT IGNORE INTO service_types (id, name, duration_minutes, description) VALUES
(1, 'Olajcsere', 30, 'Motor olaj és olajszűrő csere'),
(2, 'Fékjavítás', 90, 'Fékbetét, féktárcsa csere és beállítás'),
(3, 'Futómű javítás', 120, 'Futómű alkatrészek cseréje és beállítás'),
(4, 'Motorjavítás', 240, 'Motor diagnosztika és javítás'),
(5, 'Klíma szerviz', 60, 'Klíma tisztítás, töltés'),
(6, 'Gumiabroncs csere', 45, 'Négy kerék gumiabroncs cseréje'),
(7, 'Műszaki vizsga előkészítés', 90, 'Teljes átvizsgálás műszaki vizsgához'),
(8, 'Diagnosztika', 60, 'Elektronikus diagnosztika'),
(9, 'Akkumulátor csere', 20, 'Akkumulátor csere és ellenőrzés'),
(10, 'Szélvédő csere', 120, 'Szélvédő üveg csere');
