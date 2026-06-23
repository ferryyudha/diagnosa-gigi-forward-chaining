<?php
/**
 * LOGOUT
 * Menghancurkan session dan mengarahkan ke halaman login.
 * Memuat database.php terlebih dahulu karena mendefinisikan BASE_URL dari .env
 */
require_once '../config/database.php'; // Load .env → mendefinisikan BASE_URL
require_once '../config/session.php';  // Load fungsi session

// Hapus semua data session
$_SESSION = [];
session_destroy();

// Hapus cookie session jika ada
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

// Redirect ke halaman login
redirect(BASE_URL . '/auth/login.php');
?>
