# AlapSzerviz.hu - Autószerviz Kereső Platform

Teljes körű PHP alapú webalkalmazás autószervizek keresésére, feltöltésére és értékelésére.

## 🚀 Funkciók

### Alapfunkciók
- ✅ Felhasználói regisztráció és bejelentkezés (session alapú)
- ✅ Szervizek feltöltése (képpel, pending státusszal)
- ✅ Admin jóváhagyási rendszer
- ✅ Komment rendszer
- ✅ Support chat (AJAX auto-refresh)
- ✅ Admin panel (dashboard, moderálás)
- ✅ Reszponzív design (Bootstrap 5)
- ✅ Autós színvilág (sötétkék, szürke, sárga)

### Új Extra Funkciók ⭐
- ✅ **Fejlett keresés** - város és név szerinti szűrés
- ✅ **Dark Mode** - sötét/világos téma váltó (localStorage)
- ✅ **Prémium Partner** - kiemelt szervizek badge-dzsel
- ✅ **Statisztikák** - megtekintések számolása
- ✅ **5 csillagos értékelés** - user-ek értékelhetik a szervizeket
- ✅ **Kedvencek rendszer** - szervizek mentése
- ✅ **Smooth animációk** - fade-in, hover effektek
- ✅ **Prémium kiemelés** - sárga border, elöl a listában
- ✅ **Időpontfoglalás** - online időpont foglalási rendszer
  - Felhasználók foglalhatnak időpontot a szervizekhez
  - Foglalások kezelése a profilban
  - Admin oldali foglaláskezelés (visszaigazolás, lemondás, teljesítés)
  - Státusz követés (függőben, visszaigazolva, lemondva, teljesítve)

## 📋 Követelmények

- PHP 7.4 vagy újabb
- MySQL 5.7+ vagy MariaDB 10.3+
- Apache vagy Nginx webszerver
- mod_rewrite (opcionális)

## 🔧 Telepítés

### 1. Fájlok feltöltése

Másold az összes fájlt a webszerver gyökérkönyvtárába.

**XAMPP (Windows):**
```
C:\xampp\htdocs\teszt\
```

**XAMPP (Linux/Mac):**
```
/opt/lampp/htdocs/teszt/
```

**Éles szerver:**
```
/var/www/html/
vagy
public_html/
```

### 2. Adatbázis létrehozása

**XAMPP-ben (phpMyAdmin):**
1. Nyisd meg: `http://localhost/phpmyadmin`
2. Kattints az "Import" fülre
3. Válaszd ki a `database.sql` fájlt
4. Kattints a "Go" gombra

**Parancssorból:**
```bash
mysql -u root -p < database.sql
```

Az adatbázis neve: `alapszerviz`

### 3. Környezet beállítása

Az `includes/config.php` fájlban állítsd be a környezetet:

**XAMPP fejlesztéshez (MOST AKTÍV):**
```php
$environment = 'local'; // ✅ Jelenleg ez van beállítva
```

**Éles szerverre feltöltéskor:**
```php
$environment = 'production';
```

**Localhost beállítások (automatikus):**
- Host: `localhost`
- DB: `alapszerviz`
- User: `root`
- Pass: (üres)

**Éles szerver beállítások (automatikus):**
- Host: `localhost`
- DB: `rh57507_alapszerviz`
- User: `rh57507_alapszervizJo`
- Pass: `projectszerviz2006`

### 4. Új funkciók telepítése (ha már létező adatbázisod van)

**XAMPP-ben (phpMyAdmin):**
1. Nyisd meg: `http://localhost/phpmyadmin`
2. Válaszd ki az `alapszerviz` adatbázist
3. Kattints az "Import" fülre
4. Importáld a következő fájlokat sorrendben:
   - `database_update_all_features.sql`
   - `database_update_bookings.sql`

**Parancssorból:**
```bash
mysql -u root alapszerviz < database_update_all_features.sql
mysql -u root alapszerviz < database_update_bookings.sql
```

Ez telepíti:
- Support status mező
- Prémium és statisztika mezők
- Értékelési rendszer (ratings tábla)
- Kedvencek rendszer (favorites tábla)
- Időpontfoglalás rendszer (bookings tábla)

### 5. Uploads mappa jogosultságok

**Windows (XAMPP):**
- Általában nem kell semmit csinálni
- Ha probléma van: Jobb klikk az `uploads` mappán → Properties → Security → Edit → Add → Everyone → Full Control

**Linux/Mac:**
```bash
chmod 755 uploads/
```

### 6. Első Admin User

Az SQL script automatikusan létrehoz egy admin usert:

**Email:** `admin@alapszerviz.hu`  
**Jelszó:** `admin123`

⚠️ **FONTOS:** Változtasd meg a jelszót éles környezetben!

## 🎯 Használat

### XAMPP-ben (Localhost)
**Főoldal:**
```
http://localhost/teszt/
```

**Admin Panel:**
```
http://localhost/teszt/admin/
```

### Éles szerveren
**Főoldal:**
```
https://yourdomain.com/
```

**Admin Panel:**
```
https://yourdomain.com/admin/
```

**Bejelentkezés:**
- Email: `admin@alapszerviz.hu`
- Jelszó: `admin123`

### Teszt User (opcionális)

Az SQL script létrehoz egy teszt usert is:

**Email:** `user@alapszerviz.hu`  
**Jelszó:** `user123`

## 📁 Fájlstruktúra

```
/
├── index.php                 # Főoldal
├── login.php                 # Bejelentkezés
├── register.php              # Regisztráció
├── profile.php               # Profil
├── support.php               # Support chat
├── services.php              # Szervizek listája
├── service_add.php           # Szerviz hozzáadása
├── service_view.php          # Szerviz részletek
├── comment_add.php           # Komment POST handler
├── booking_create.php        # Időpont foglalás
├── booking_cancel.php        # Foglalás lemondás
├── rate_service.php          # Értékelés POST handler
├── toggle_favorite.php       # Kedvenc hozzáadás/eltávolítás
├── logout.php                # Kijelentkezés
├── database.sql              # Adatbázis script
├── README.md                 # Ez a fájl
├── uploads/                  # Feltöltött képek
├── includes/
│   ├── db.php               # PDO kapcsolat
│   ├── auth.php             # Session kezelés
│   ├── header.php           # Közös header
│   └── footer.php           # Közös footer
└── admin/
    ├── index.php            # Admin dashboard
    ├── login.php            # Admin bejelentkezés
    ├── services.php         # Szervizek kezelése
    ├── service_approve.php  # Jóváhagyás handler
    ├── service_delete.php   # Törlés handler
    ├── service_toggle_premium.php  # Prémium státusz váltás
    ├── comments.php         # Kommentek moderálása
    ├── users.php            # Felhasználók
    ├── support.php          # Support üzenetek kezelése
    ├── bookings.php         # Foglalások kezelése
    ├── logout.php           # Admin kijelentkezés
    ├── header.php           # Admin header
    └── footer.php           # Admin footer
```

## 🎨 Design

### Színséma
- **Sötétkék:** `#1a2332` (primary)
- **Szürke:** `#6c757d` (secondary)
- **Sárga:** `#ffc107` (accent)
- **Világos háttér:** `#f8f9fa`

### Reszponzív Breakpointok
- **Mobile:** < 576px
- **Tablet:** 576px - 992px
- **Desktop:** > 992px

## 🔒 Biztonság

- ✅ Password hash (bcrypt)
- ✅ PDO prepared statements (SQL injection védelem)
- ✅ htmlspecialchars() (XSS védelem)
- ✅ Session alapú authentikáció
- ✅ Fájl feltöltés validáció (típus, méret)
- ✅ Email uniqueness ellenőrzés
- ✅ Admin jogosultság ellenőrzés

## 📊 Adatbázis Táblák

### users
- Felhasználók (name, email, password, is_admin)

### services
- Szervizek (name, city, address, description, phone, hours, image, status)

### comments
- Kommentek (service_id, user_id, comment)

### support
- Support üzenetek (user_id, admin_id, message, from_admin)

### ratings
- Értékelések (service_id, user_id, rating)

### favorites
- Kedvenc szervizek (user_id, service_id)

### bookings
- Időpontfoglalások (user_id, service_id, booking_date, booking_time, name, phone, email, message, status)

## 🐛 Hibaelhárítás

### "500 Internal Server Error" vagy ".htaccess hiba"
- Próbáld átnevezni a `.htaccess` fájlt `.htaccess.backup`-ra
- Másold át a `.htaccess.simple` fájlt `.htaccess` névre
- Ha továbbra sem működik, töröld a `.htaccess` fájlt (nem kötelező)
- Ellenőrizd a szerver error log-ját (általában a cPanel-ben elérhető)

### "Adatbázis kapcsolati hiba"
- Ellenőrizd az `includes/db.php` beállításokat
- Győződj meg róla, hogy a MySQL szerver fut
- Ellenőrizd a felhasználónevet és jelszót
- Éles szerveren: `rh57507_alapszerviz` / `rh57507_alapszervizJo` / `projectszerviz2006`
- Helyi szerveren (XAMPP): `alapszerviz` / `root` / (üres jelszó)

### "Hiba történt a kép feltöltése során"
- Ellenőrizd az `uploads/` mappa jogosultságait
- Győződj meg róla, hogy a mappa létezik
- Ellenőrizd a PHP `upload_max_filesize` beállítást

### "Session hiba"
- Ellenőrizd, hogy a PHP session_start() működik
- Nézd meg a PHP error log-ot
- Ellenőrizd a session mappa jogosultságait

### Admin nem tud bejelentkezni
- Használd az SQL script-et az admin user létrehozásához
- Ellenőrizd, hogy az `is_admin` mező értéke 1
- Próbáld meg újra importálni a `database.sql` fájlt

## 📝 Fejlesztési Lehetőségek

- [ ] Keresés és szűrés (város, név)
- [ ] 5 csillagos értékelés rendszer
- [ ] Több kép feltöltése szervizhez
- [ ] Email értesítések
- [ ] Jelszó visszaállítás
- [ ] Profil kép feltöltés
- [ ] Kedvencek funkció
- [ ] Export funkció (CSV)
- [ ] Grafikonok az admin dashboardon

## 👨‍💻 Technológiák

- **Backend:** PHP 7.4+ (natív, framework nélkül)
- **Frontend:** HTML5, Bootstrap 5.3, JavaScript
- **Adatbázis:** MySQL / MariaDB
- **Ikonok:** Bootstrap Icons
- **Session:** PHP $_SESSION

## 📄 Licenc

Ez a projekt oktatási célokra készült.

## 🤝 Támogatás

Ha problémád van, használd a support chat funkciót az oldalon, vagy írj emailt: info@alapszerviz.hu

---

**Készítve ❤️-tel Magyarországon**

🚗 AlapSzerviz.hu - Találd meg a legjobb autószervizt!
