<?php
/**
 * =====================================================
 * SESSION & KEAMANAN
 * =====================================================
 * File ini menangani session PHP dan fungsi-fungsi
 * keamanan yang dipakai di seluruh aplikasi.
 *
 * CATATAN: BASE_URL, APP_NAME, dll sudah didefinisikan
 * di config/database.php yang membacanya dari .env.
 * Jadi JANGAN define ulang di sini.
 * =====================================================
 */

// Mulai session PHP
session_start();

// -------------------------------------------------------
// Fungsi Keamanan & Akses
// -------------------------------------------------------

/**
 * Pastikan user sudah login.
 * Jika belum, redirect ke halaman login.
 */
function requireLogin(): void {
    if (!isset($_SESSION['user_id'])) {
        header('Location: ' . BASE_URL . '/auth/login.php');
        exit;
    }
}

/**
 * Pastikan user adalah admin.
 * Jika bukan, redirect ke halaman utama.
 */
function requireAdmin(): void {
    requireLogin();
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . BASE_URL . '/index.php');
        exit;
    }
}

// -------------------------------------------------------
// Fungsi Utility
// -------------------------------------------------------

/**
 * Bersihkan input dari karakter berbahaya (XSS Prevention).
 * SELALU gunakan fungsi ini sebelum menampilkan data dari user.
 *
 * @param  mixed $str  Input yang akan dibersihkan
 * @return string      String yang sudah aman ditampilkan ke HTML
 */
function clean(mixed $str): string {
    return htmlspecialchars(strip_tags(trim((string)$str)), ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect ke URL tertentu dan hentikan eksekusi.
 */
function redirect(string $url): never {
    header('Location: ' . $url);
    exit;
}

/**
 * Simpan flash message ke session (ditampilkan sekali di halaman berikutnya).
 *
 * @param string $type    Tipe pesan: 'success' | 'danger' | 'warning' | 'info'
 * @param string $message Isi pesan
 */
function setFlash(string $type, string $message): void {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

/**
 * Ambil dan hapus flash message dari session.
 * Mengembalikan null jika tidak ada flash message.
 *
 * @return array|null ['type' => ..., 'message' => ...]
 */
function getFlash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']); // Hapus setelah dibaca (one-time use)
        return $flash;
    }
    return null;
}
