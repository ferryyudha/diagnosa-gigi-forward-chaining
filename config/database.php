<?php
/**
 * =====================================================
 * KONFIGURASI DATABASE
 * =====================================================
 * File ini membaca semua pengaturan koneksi database
 * dari file .env yang ada di root project.
 *
 * Cara mengubah konfigurasi:
 *   → Buka file .env di root folder
 *   → Ubah nilai DB_HOST, DB_NAME, DB_USER, DB_PASS
 *   → Simpan → selesai! Tidak perlu ubah file PHP apapun.
 * =====================================================
 */

// Muat ENV loader terlebih dahulu
require_once __DIR__ . '/env.php';

// Tentukan path root project dan muat .env
$envPath = dirname(__DIR__) . '/.env';
loadEnv($envPath);

// -------------------------------------------------------
// Baca variabel dari .env menggunakan helper env()
// -------------------------------------------------------
define('DB_HOST', env('DB_HOST', 'localhost'));   // Host database
define('DB_PORT', (int) env('DB_PORT', 3306));     // Port MySQL
define('DB_NAME', env('DB_NAME', 'db_gigi'));      // Nama database
define('DB_USER', env('DB_USER', 'root'));          // Username MySQL
define('DB_PASS', env('DB_PASS', ''));              // Password MySQL

// -------------------------------------------------------
// Konstanta Aplikasi dari .env
// -------------------------------------------------------
define('APP_NAME',    env('APP_NAME', 'SiPaGi'));
define('BASE_URL',    env('APP_URL',  'http://localhost/forward_chaining'));
define('APP_ENV',     env('APP_ENV',  'development'));

// Info Klinik
define('KLINIK_NAMA',   env('KLINIK_NAMA',   'Praktik Mandiri Drg. Hj. Rini Sutarti'));
define('KLINIK_ALAMAT', env('KLINIK_ALAMAT', '-'));
define('KLINIK_TELP',   env('KLINIK_TELP',   '-'));

/**
 * Membuat koneksi ke database MySQL menggunakan MySQLi.
 * Dipanggil sekali di setiap halaman via require_once.
 *
 * @return mysqli Objek koneksi yang sudah siap digunakan
 */
function getConnection(): mysqli {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

    if ($conn->connect_error) {
        $isDev = APP_ENV === 'development';

        // Pesan error detail hanya ditampilkan di mode development
        $detail = $isDev
            ? '<p><strong>Detail Error:</strong> ' . htmlspecialchars($conn->connect_error) . '</p>
               <p><strong>Konfigurasi saat ini:</strong></p>
               <ul style="margin:8px 0 0 20px;line-height:1.8">
                   <li>Host: <code>' . DB_HOST . ':' . DB_PORT . '</code></li>
                   <li>Database: <code>' . DB_NAME . '</code></li>
                   <li>User: <code>' . DB_USER . '</code></li>
               </ul>
               <p style="margin-top:12px">
                   📝 Periksa file <code>.env</code> di root project dan pastikan<br>
                   XAMPP MySQL sudah berjalan di XAMPP Control Panel.
               </p>'
            : '<p>Hubungi administrator sistem.</p>';

        die('<!DOCTYPE html><html><head><meta charset="UTF-8">
            <style>
                body{font-family:Arial,sans-serif;background:#0a0f1e;color:#f0f6ff;display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0}
                .box{background:#1a0a0a;border:1px solid #ef4444;border-radius:12px;padding:28px;max-width:500px;width:90%}
                h3{color:#ef4444;margin:0 0 12px}
                code{background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px;font-size:13px}
                p{color:#94a3b8;font-size:14px;line-height:1.6;margin:8px 0}
            </style></head><body>
            <div class="box">
                <h3>❌ Koneksi Database Gagal!</h3>' . $detail . '
            </div></body></html>');
    }

    // Set charset UTF-8 agar karakter Indonesia tampil benar
    $conn->set_charset('utf8mb4');

    return $conn;
}

// -------------------------------------------------------
// Buat koneksi global yang siap dipakai di semua halaman
// -------------------------------------------------------
$conn = getConnection();
