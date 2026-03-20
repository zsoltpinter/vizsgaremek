<?php
/**
 * API Konfigurációs Fájl
 * 
 * FONTOS: Regisztrálj egy ingyenes API kulcsot itt:
 * https://openweathermap.org/api
 * 
 * Lépések:
 * 1. Regisztrálj a https://openweathermap.org/appid oldalon
 * 2. Aktiváld az API kulcsot (email-ben kapod)
 * 3. Másold be ide az API kulcsot
 * 4. Az API kulcs aktiválása 1-2 órát vehet igénybe
 */

// OpenWeatherMap API kulcs
define('OPENWEATHER_API_KEY', 'c05ddbaa0a686e455f1c8dae2ac9dcf0');

// API beállítások
define('OPENWEATHER_CITY', 'Budapest,hu');
define('OPENWEATHER_UNITS', 'metric'); // metric = Celsius
define('OPENWEATHER_LANG', 'hu'); // magyar nyelv
define('API_TIMEOUT', 5); // másodperc
