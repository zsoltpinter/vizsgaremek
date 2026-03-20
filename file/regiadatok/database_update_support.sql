-- Support tábla frissítése - status mező hozzáadása
-- Futtasd ezt ha már létező adatbázisod van és frissíteni szeretnéd

USE alapszerviz;

-- Status mező hozzáadása ha még nincs
ALTER TABLE support 
ADD COLUMN status ENUM('open', 'closed') DEFAULT 'open' AFTER from_admin;

-- Index hozzáadása a status mezőhöz
ALTER TABLE support 
ADD INDEX idx_status (status);

-- Minden létező üzenet legyen 'open' státuszú
UPDATE support SET status = 'open' WHERE status IS NULL;

SELECT 'Support tábla sikeresen frissítve!' as message;
