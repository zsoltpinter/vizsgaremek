<?php
/**
 * Adatbázis kapcsolat - PDO Singleton Pattern
 */

function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        $host = 'localhost';
        $dbname = 'rh57507_alapszeviz';
        $username = 'cpses_rhpyw5zl1f@localhost';
        $password = 'projectszerviz2006';
        
        try {
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Adatbázis kapcsolati hiba: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
