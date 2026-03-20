-- Prémium és statisztika mezők hozzáadása
-- Futtasd ezt ha már létező adatbázisod van

USE alapszerviz;

-- Prémium mező hozzáadása
ALTER TABLE services 
ADD COLUMN is_premium TINYINT(1) DEFAULT 0 AFTER status;

-- Megtekintések számláló
ALTER TABLE services 
ADD COLUMN views INT DEFAULT 0 AFTER is_premium;

-- Index hozzáadása
ALTER TABLE services 
ADD INDEX idx_is_premium (is_premium);

-- Minden létező szerviz legyen nem prémium
UPDATE services SET is_premium = 0 WHERE is_premium IS NULL;
UPDATE services SET views = 0 WHERE views IS NULL;

SELECT 'Services tábla sikeresen frissítve prémium funkciókkal!' as message;
