<?php
/**
 * API Integráció és Cache Kezelés
 * Autós témájú adatok lekérése és cache-elése
 */

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/api_config.php';

/**
 * Részletes időjárási adatok lekérése vezetési körülményekkel
 */
function get_weather_for_drivers() {
    $cache_key = 'weather_driving_conditions';
    
    // Cache ellenőrzés
    $cached = get_cached_data($cache_key);
    if ($cached !== null) {
        return $cached;
    }
    
    // API hívás
    try {
        $api_key = OPENWEATHER_API_KEY;
        $city = OPENWEATHER_CITY;
        $units = OPENWEATHER_UNITS;
        $lang = OPENWEATHER_LANG;
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$api_key}&units={$units}&lang={$lang}";
        
        $context = stream_context_create([
            'http' => [
                'timeout' => API_TIMEOUT,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $data = json_decode($response, true);
            
            if (isset($data['main']) && isset($data['weather'])) {
                $weather_info = [
                    'temperature' => round($data['main']['temp']),
                    'feels_like' => round($data['main']['feels_like']),
                    'description' => ucfirst($data['weather'][0]['description'] ?? 'Ismeretlen'),
                    'humidity' => $data['main']['humidity'] ?? 0,
                    'pressure' => $data['main']['pressure'] ?? 0,
                    'wind_speed' => round(($data['wind']['speed'] ?? 0) * 3.6), // m/s -> km/h
                    'wind_deg' => $data['wind']['deg'] ?? 0,
                    'visibility' => isset($data['visibility']) ? round($data['visibility'] / 1000, 1) : 10, // méter -> km
                    'clouds' => $data['clouds']['all'] ?? 0,
                    'icon' => $data['weather'][0]['icon'] ?? '01d',
                    'main' => $data['weather'][0]['main'] ?? 'Clear'
                ];
                
                // Vezetési körülmények értékelése
                $driving_conditions = evaluate_driving_conditions($weather_info);
                $weather_info['driving_conditions'] = $driving_conditions;
                
                // Cache 30 percre
                cache_api_data($cache_key, $weather_info, 1800);
                
                return $weather_info;
            }
        }
    } catch (Exception $e) {
        // Hiba esetén fallback
    }
    
    // Fallback adatok
    return [
        'temperature' => 15,
        'feels_like' => 14,
        'description' => 'Derült',
        'humidity' => 60,
        'pressure' => 1013,
        'wind_speed' => 10,
        'wind_deg' => 180,
        'visibility' => 10,
        'clouds' => 20,
        'icon' => '01d',
        'main' => 'Clear',
        'driving_conditions' => [
            'overall' => 'good',
            'overall_text' => 'Jó vezetési körülmények',
            'warnings' => [],
            'tips' => ['Normál vezetési körülmények várhatók']
        ]
    ];
}

/**
 * Vezetési körülmények értékelése időjárás alapján
 */
function evaluate_driving_conditions($weather) {
    $warnings = [];
    $tips = [];
    $overall = 'good'; // good, moderate, poor
    
    // Hőmérséklet ellenőrzés
    if ($weather['temperature'] < 0) {
        $warnings[] = 'Fagypont alatti hőmérséklet - jegesedés veszélye!';
        $tips[] = 'Vezess óvatosan, számíts jeges útfelületekre';
        $overall = 'poor';
    } elseif ($weather['temperature'] < 5) {
        $warnings[] = 'Hideg idő - téli gumik ajánlottak';
        $tips[] = 'Ellenőrizd a téli gumikat és az akkumulátort';
        if ($overall === 'good') $overall = 'moderate';
    }
    
    // Látótávolság ellenőrzés
    if ($weather['visibility'] < 1) {
        $warnings[] = 'Nagyon rossz látási viszonyok - sűrű köd!';
        $tips[] = 'Használd a ködlámpát és csökkentsd a sebességet';
        $overall = 'poor';
    } elseif ($weather['visibility'] < 5) {
        $warnings[] = 'Korlátozott látótávolság - köd';
        $tips[] = 'Vezess óvatosan, tartsd be a követési távolságot';
        if ($overall === 'good') $overall = 'moderate';
    }
    
    // Szél ellenőrzés
    if ($weather['wind_speed'] > 50) {
        $warnings[] = 'Viharos szél - veszélyes vezetési körülmények!';
        $tips[] = 'Kerüld a nagy sebességet, figyelj az oldalszélre';
        $overall = 'poor';
    } elseif ($weather['wind_speed'] > 30) {
        $warnings[] = 'Erős szél';
        $tips[] = 'Vezess óvatosan, különösen hídon és nyílt terepen';
        if ($overall === 'good') $overall = 'moderate';
    }
    
    // Páratartalom ellenőrzés
    if ($weather['humidity'] > 90) {
        $warnings[] = 'Nagyon magas páratartalom';
        $tips[] = 'Figyelj a párásodó szélvédőre, használd a klímát';
        if ($overall === 'good') $overall = 'moderate';
    }
    
    // Időjárás típus ellenőrzés
    $main = strtolower($weather['main']);
    if (in_array($main, ['rain', 'drizzle', 'thunderstorm'])) {
        $warnings[] = 'Esős idő - csúszós útfelület';
        $tips[] = 'Csökkentsd a sebességet, tartsd be a követési távolságot';
        if ($overall === 'good') $overall = 'moderate';
    } elseif ($main === 'snow') {
        $warnings[] = 'Havazás - veszélyes útfelület!';
        $tips[] = 'Téli gumik kötelezőek, vezess nagyon óvatosan';
        $overall = 'poor';
    } elseif ($main === 'mist' || $main === 'fog') {
        $warnings[] = 'Köd - rossz látási viszonyok';
        $tips[] = 'Használd a ködlámpát, csökkentsd a sebességet';
        if ($overall === 'good') $overall = 'moderate';
    }
    
    // Ha nincs figyelmeztetés
    if (empty($warnings)) {
        $tips[] = 'Jó vezetési körülmények várhatók';
    }
    
    // Összesített értékelés szöveg
    $overall_texts = [
        'good' => 'Jó vezetési körülmények',
        'moderate' => 'Közepes vezetési körülmények - óvatosság ajánlott',
        'poor' => 'Rossz vezetési körülmények - fokozott óvatosság!'
    ];
    
    return [
        'overall' => $overall,
        'overall_text' => $overall_texts[$overall],
        'warnings' => $warnings,
        'tips' => $tips
    ];
}

/**
 * Szél irány szöveges megjelenítése
 */
function get_wind_direction($degrees) {
    $directions = [
        'É' => [0, 22.5],
        'ÉK' => [22.5, 67.5],
        'K' => [67.5, 112.5],
        'DK' => [112.5, 157.5],
        'D' => [157.5, 202.5],
        'DNY' => [202.5, 247.5],
        'NY' => [247.5, 292.5],
        'ÉNY' => [292.5, 337.5],
        'É' => [337.5, 360]
    ];
    
    foreach ($directions as $dir => $range) {
        if ($degrees >= $range[0] && $degrees < $range[1]) {
            return $dir;
        }
    }
    
    return 'É';
}

/**
 * Autós témájú adatok lekérése (cache-elt vagy API-ból)
 */
function get_automotive_data() {
    $cache_key = 'automotive_news';
    
    // Először próbáljuk meg a cache-ből
    $cached = get_cached_data($cache_key);
    if ($cached !== null) {
        return $cached;
    }
    
    // Ha nincs cache, próbáljuk meg az API-t
    try {
        // Használjuk a NewsAPI-t autós hírekhez (ingyenes)
        // Vagy fallback alapértelmezett adatokra
        $data = fetch_automotive_news();
        
        // Cache-eljük 1 órára
        cache_api_data($cache_key, $data, 3600);
        
        return $data;
    } catch (Exception $e) {
        // Ha az API nem elérhető, alapértelmezett tartalom
        return get_default_automotive_data();
    }
}

/**
 * Autós hírek lekérése API-ból vagy fallback
 */
function fetch_automotive_news() {
    // OpenWeatherMap API használata (ingyenes)
    $api_key = OPENWEATHER_API_KEY;
    
    // Próbáljuk meg lekérni az időjárást
    try {
        $city = OPENWEATHER_CITY;
        $units = OPENWEATHER_UNITS;
        $lang = OPENWEATHER_LANG;
        $url = "https://api.openweathermap.org/data/2.5/weather?q={$city}&appid={$api_key}&units={$units}&lang={$lang}";
        
        // API hívás
        $context = stream_context_create([
            'http' => [
                'timeout' => API_TIMEOUT,
                'ignore_errors' => true
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response !== false) {
            $weather_data = json_decode($response, true);
            
            // Ha sikeres az API hívás
            if (isset($weather_data['main']) && isset($weather_data['weather'])) {
                $temp = round($weather_data['main']['temp']);
                $description = $weather_data['weather'][0]['description'] ?? 'Ismeretlen';
                $humidity = $weather_data['main']['humidity'] ?? 0;
                $wind_speed = round($weather_data['wind']['speed'] ?? 0);
                
                // Időjárás alapú autós tanácsok
                $weather_tips = [];
                if ($temp < 5) {
                    $weather_tips[] = 'Hideg idő! Ellenőrizd a téli gumikat és az akkumulátort.';
                }
                if ($humidity > 80) {
                    $weather_tips[] = 'Magas páratartalom! Figyelj a párásodó szélvédőre.';
                }
                if ($wind_speed > 30) {
                    $weather_tips[] = 'Erős szél! Vezess óvatosan, különösen hídon és nyílt terepen.';
                }
                
                return [
                    'news' => [
                        [
                            'title' => "Aktuális időjárás Budapesten",
                            'summary' => "{$temp}°C, {$description}. Páratartalom: {$humidity}%, Szél: {$wind_speed} km/h",
                            'icon' => 'bi-cloud-sun-fill'
                        ],
                        [
                            'title' => 'Elektromos autók térnyerése 2024-ben',
                            'summary' => 'Az elektromos járművek piaca folyamatosan bővül Magyarországon is.',
                            'icon' => 'bi-ev-station-fill'
                        ],
                        [
                            'title' => 'Téli gumi kötelezettség',
                            'summary' => 'Ne felejtsd el időben felszerelni a téli gumikat!',
                            'icon' => 'bi-snow'
                        ],
                        [
                            'title' => 'Műszaki vizsga határidők',
                            'summary' => 'Ellenőrizd az autód műszaki vizsgájának érvényességét!',
                            'icon' => 'bi-clipboard-check'
                        ]
                    ],
                    'tips' => array_merge(
                        $weather_tips,
                        [
                            'Rendszeres olajcsere 10-15 ezer km-enként',
                            'Fékfolyadék csere 2 évente',
                            'Légszűrő csere évente',
                            'Gumiabroncs nyomás ellenőrzés havonta'
                        ]
                    ),
                    'api_source' => 'OpenWeatherMap API',
                    'last_updated' => date('Y-m-d H:i:s')
                ];
            }
        }
    } catch (Exception $e) {
        // Ha hiba van, fallback-re váltunk
    }
    
    // Fallback: Alapértelmezett autós hírek (ha az API nem elérhető)
    return [
        'news' => [
            [
                'title' => 'Elektromos autók térnyerése 2024-ben',
                'summary' => 'Az elektromos járművek piaca folyamatosan bővül Magyarországon is.',
                'icon' => 'bi-ev-station-fill'
            ],
            [
                'title' => 'Téli gumi kötelezettség',
                'summary' => 'Ne felejtsd el időben felszerelni a téli gumikat!',
                'icon' => 'bi-snow'
            ],
            [
                'title' => 'Autószerviz tippek',
                'summary' => 'Rendszeres karbantartással megelőzheted a drága javításokat.',
                'icon' => 'bi-tools'
            ],
            [
                'title' => 'Műszaki vizsga határidők',
                'summary' => 'Ellenőrizd az autód műszaki vizsgájának érvényességét!',
                'icon' => 'bi-clipboard-check'
            ]
        ],
        'tips' => [
            'Rendszeres olajcsere 10-15 ezer km-enként',
            'Fékfolyadék csere 2 évente',
            'Légszűrő csere évente',
            'Gumiabroncs nyomás ellenőrzés havonta'
        ],
        'api_source' => 'Fallback (API nem elérhető)',
        'last_updated' => date('Y-m-d H:i:s')
    ];
}

/**
 * Alapértelmezett autós adatok (fallback)
 */
function get_default_automotive_data() {
    return fetch_automotive_news(); // Ugyanaz, mint a fallback
}

/**
 * Cache-elt adat lekérése
 */
function get_cached_data($cache_key) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT cache_data, expires_at 
            FROM api_cache 
            WHERE cache_key = ? AND expires_at > NOW()
        ");
        $stmt->execute([$cache_key]);
        $result = $stmt->fetch();
        
        if ($result) {
            return json_decode($result['cache_data'], true);
        }
        
        return null;
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Adat cache-elése
 */
function cache_api_data($cache_key, $data, $ttl = 3600) {
    try {
        $pdo = getDB();
        $expires_at = date('Y-m-d H:i:s', time() + $ttl);
        $cache_data = json_encode($data);
        
        // INSERT vagy UPDATE
        $stmt = $pdo->prepare("
            INSERT INTO api_cache (cache_key, cache_data, expires_at) 
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE 
                cache_data = VALUES(cache_data),
                expires_at = VALUES(expires_at),
                created_at = NOW()
        ");
        $stmt->execute([$cache_key, $cache_data, $expires_at]);
        
        return true;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Cache érvényességének ellenőrzése
 */
function is_cache_valid($cache_key) {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as count 
            FROM api_cache 
            WHERE cache_key = ? AND expires_at > NOW()
        ");
        $stmt->execute([$cache_key]);
        $result = $stmt->fetch();
        
        return $result['count'] > 0;
    } catch (Exception $e) {
        return false;
    }
}

/**
 * Lejárt cache bejegyzések törlése
 */
function clear_expired_cache() {
    try {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM api_cache WHERE expires_at < NOW()");
        $stmt->execute();
        return true;
    } catch (Exception $e) {
        return false;
    }
}
