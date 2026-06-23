<?php
// Konfigurasi koneksi database — baca dari file .env

require_once __DIR__ . '/env.php';

$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// Ambil nilai dari .env, kalau tidak ada pakai default
define('DB_HOST', env('DB_HOST', 'localhost'));
define('DB_PORT', (int) env('DB_PORT', 3306));
define('DB_NAME', env('DB_NAME', 'db_gigi'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));

define('APP_NAME', env('APP_NAME', 'SiPaGi'));

// Deteksi BASE_URL — beda antara localhost dan server hosting
$defaultAppUrl = 'http://localhost/forward_chaining';
if (isset($_SERVER['HTTP_HOST'])) {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
    $protocol = $isHttps ? 'https://' : 'http://';
    $isLocalhost = str_contains($_SERVER['HTTP_HOST'], 'localhost') || str_contains($_SERVER['HTTP_HOST'], '127.0.0.1');
    $subDir = $isLocalhost ? '/forward_chaining' : '';
    $defaultAppUrl = $protocol . $_SERVER['HTTP_HOST'] . $subDir;
}
define('BASE_URL', env('APP_URL', $defaultAppUrl));
define('APP_ENV',  env('APP_ENV', 'development'));

if (APP_ENV === 'development') {
    ini_set('display_errors', '1');
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', '0');
    error_reporting(0);
}

// Info klinik
define('KLINIK_NAMA',   env('KLINIK_NAMA',   'Praktik Mandiri Drg. Hj. Rini Sutarti'));
define('KLINIK_ALAMAT', env('KLINIK_ALAMAT', '-'));
define('KLINIK_TELP',   env('KLINIK_TELP',   '-'));

// Buat koneksi ke MySQL
function getConnection() {
    $conn = @new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        $pesan = 'Koneksi database gagal: ' . $conn->connect_error;
        if (APP_ENV === 'development') {
            $pesan .= ' (Host: ' . DB_HOST . ', DB: ' . DB_NAME . ')';
        }
        die($pesan);
    }

    $conn->set_charset('utf8mb4');
    return $conn;
}

$conn = getConnection();
