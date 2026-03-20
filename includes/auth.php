<?php
/**
 * Authentikáció és jogosultságkezelés
 */

// Session indítása ha még nincs
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Ellenőrzi hogy a user be van-e jelentkezve
 */
function is_logged_in() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Ellenőrzi hogy a user admin-e
 */
function is_admin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

/**
 * Visszaadja a bejelentkezett user ID-t
 */
function get_current_user_id() {
    return is_logged_in() ? $_SESSION['user_id'] : null;
}

/**
 * Visszaadja a bejelentkezett user nevét
 */
function get_current_user_name() {
    return is_logged_in() ? $_SESSION['user_name'] : null;
}

/**
 * Megköveteli a bejelentkezést - átirányít ha nincs
 */
function require_login() {
    if (!is_logged_in()) {
        header('Location: /login.php');
        exit;
    }
}

/**
 * Megköveteli az admin jogosultságot - átirányít ha nincs
 */
function require_admin() {
    if (!is_admin()) {
        header('Location: /index.php');
        exit;
    }
}

/**
 * Bejelentkeztet egy usert
 */
function login_user($user) {
    $_SESSION['user_id'] = $user['id'];
    $_SESSION['user_name'] = $user['name'];
    $_SESSION['user_email'] = $user['email'];
    
    if ($user['is_admin'] == 1) {
        $_SESSION['is_admin'] = true;
    }
    
    // Session regenerate a biztonság érdekében
    session_regenerate_id(true);
}

/**
 * Kijelentkeztet egy usert
 */
function logout_user() {
    $_SESSION = array();
    
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    
    session_destroy();
}
