# 🚀 Éles Szerverre Telepítés - AlapSzerviz.hu

## 📋 Előkészületek

### 1. Fájlok feltöltése
Töltsd fel az összes fájlt az éles szerverre FTP-n keresztül (pl. FileZilla):
- Célmappa: `public_html/` vagy `www/`
- Minden fájlt és mappát másolj át

### 2. Környezet beállítása

A `includes/config.php` fájl már be van állítva production módra:
```php
$environment = 'production'; // ✅ Már beállítva
```

**Adatbázis beállítások (automatikus):**
- Host: `localhost`
- Database: `rh57507_alapszerviz`
- User: `rh57507_alapszervizJo`
- Password: `projectszerviz2006`

## 🗄️ Adatbázis Telepítés

### Módszer 1: cPanel phpMyAdmin (Ajánlott)

1. **Jelentkezz be a cPanel-be**
   - URL: `https://yourdomain.com:2083` vagy hosting provider által megadott

2. **Nyisd meg a phpMyAdmin-t**
   - Keresd meg a "Databases" szekciót
   - Kattints a "phpMyAdmin" ikonra

3. **Válaszd ki az adatbázist**
   - Bal oldali menüben: `rh57507_alapszerviz`

4. **Importáld az SQL fájlt**
   - Kattints az "Import" fülre
   - "Choose File" → Válaszd ki: `database_production.sql`
   - Scroll le és kattints a "Go" gombra
   - ⏱️ Várj, amíg befejeződik (1-2 perc)

5. **Ellenőrzés**
   - Nézd meg, hogy létrejöttek-e a táblák:
     - users
     - services
     - comments
     - support
     - ratings
     - favorites
     - bookings
     - service_types

### Módszer 2: SSH (Haladó)

Ha van SSH hozzáférésed:

```bash
mysql -u rh57507_alapszervizJo -p rh57507_alapszerviz < database_production.sql
# Jelszó: projectszerviz2006
```

## 📁 Jogosultságok Beállítása

### uploads mappa

**cPanel File Manager:**
1. Navigálj az `uploads/` mappához
2. Jobb klikk → "Change Permissions"
3. Állítsd be: `755` (rwxr-xr-x)
4. ✅ Kattints "Change Permissions"

**FTP (FileZilla):**
1. Jobb klikk az `uploads/` mappán
2. "File permissions..."
3. Numeric value: `755`
4. ✅ OK

**SSH:**
```bash
chmod 755 uploads/
```

## 🔒 Biztonság

### 1. Admin jelszó megváltoztatása

**FONTOS!** Változtasd meg az admin jelszót az első bejelentkezés után:

1. Jelentkezz be: `https://yourdomain.com/admin/`
   - Email: `admin@alapszerviz.hu`
   - Jelszó: `admin123`

2. Menj a Users menübe
3. Szerkeszd az admin usert
4. Állíts be új, erős jelszót

### 2. .htaccess ellenőrzése

A `.htaccess` fájl már fel van töltve és tartalmazza:
- URL átírási szabályokat
- Biztonságos fájl hozzáférést
- PHP beállításokat

Ha problémád van:
- Próbáld átnevezni `.htaccess.simple`-re
- Vagy töröld a `.htaccess` fájlt

### 3. Hibakezelés

Production módban a hibák **nem** jelennek meg a felhasználóknak.
Nézd meg a szerver error log-ját a cPanel-ben:
- cPanel → "Errors" → "Error Log"

## ✅ Ellenőrző Lista

- [ ] Fájlok feltöltve FTP-n
- [ ] `includes/config.php` → `$environment = 'production'`
- [ ] Adatbázis importálva (`database_production.sql`)
- [ ] `uploads/` mappa jogosultságok: 755
- [ ] Admin jelszó megváltoztatva
- [ ] Weboldal működik: `https://yourdomain.com`
- [ ] Admin panel működik: `https://yourdomain.com/admin/`
- [ ] Regisztráció működik
- [ ] Szerviz feltöltés működik
- [ ] Képfeltöltés működik

## 🧪 Tesztelés

### 1. Főoldal
```
https://yourdomain.com
```
- ✅ Betöltődik
- ✅ Dark mode működik
- ✅ Navbar működik

### 2. Regisztráció
```
https://yourdomain.com/register.php
```
- ✅ Új user létrehozása
- ✅ Bejelentkezés az új userrel

### 3. Szerviz feltöltés
```
https://yourdomain.com/service_add.php
```
- ✅ Képfeltöltés működik
- ✅ Szerviz pending státuszban

### 4. Admin panel
```
https://yourdomain.com/admin/
```
- ✅ Bejelentkezés
- ✅ Dashboard betöltődik
- ✅ Szerviz jóváhagyás működik

### 5. Időpontfoglalás
```
https://yourdomain.com/booking_create.php?service_id=1
```
- ✅ Szolgáltatások kiválasztása
- ✅ Időpontok betöltődnek
- ✅ Foglalás létrehozása

## 🐛 Gyakori Problémák

### "Database connection error"
- Ellenőrizd a `includes/config.php` beállításokat
- Győződj meg róla, hogy az adatbázis létezik
- Ellenőrizd a user jogosultságokat

### "500 Internal Server Error"
- Nézd meg az error log-ot (cPanel)
- Ellenőrizd a `.htaccess` fájlt
- Próbáld törölni a `.htaccess` fájlt

### Képek nem töltődnek fel
- Ellenőrizd az `uploads/` mappa jogosultságait (755)
- Nézd meg a PHP `upload_max_filesize` beállítást (cPanel → PHP Settings)

### Dark mode nem működik
- Töröld a böngésző cache-t (Ctrl+F5)
- Ellenőrizd, hogy a JavaScript betöltődik-e

## 📞 Support

Ha problémád van:
1. Nézd meg az error log-ot
2. Ellenőrizd a fenti ellenőrző listát
3. Próbáld újra importálni az adatbázist

## 🎉 Sikeres Telepítés!

Ha minden működik:
- ✅ Weboldal elérhető
- ✅ Regisztráció működik
- ✅ Admin panel működik
- ✅ Képfeltöltés működik
- ✅ Időpontfoglalás működik

**Gratulálunk! Az AlapSzerviz.hu élesben van! 🚗✨**

---

**Készítve:** 2024
**Verzió:** 1.0 Production Ready
