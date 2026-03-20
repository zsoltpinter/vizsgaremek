<?php
/**
 * Konfiguráció - Környezet beállítások
 * 
 * Állítsd át a $environment változót 'local'-ra helyi fejlesztéshez
 * vagy 'production'-ra éles szerveren
 */

// Környezet beállítása
$environment = 'production'; // 'local' vagy 'production'

// Adatbázis beállítások környezet szerint
if ($environment === 'production') {
    // Éles szerver
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'rh57507_alapszerviz');
    define('DB_USER', 'rh57507_alapszervizJo');
    define('DB_PASS', 'projectszerviz2006');
    define('DISPLAY_ERRORS', false);
} else {
    // Helyi fejlesztés (XAMPP/WAMP)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'alapszerviz');
    define('DB_USER', 'root');
    define('DB_PASS', '');
    define('DISPLAY_ERRORS', true);
}

// Hibakezelés
if (DISPLAY_ERRORS) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
