<?php
/**
 * =====================================================
 * ENV LOADER - Pembaca file .env
 * =====================================================
 * Fungsi ini membaca file .env dari root project dan
 * memasukkan setiap variabel ke dalam $_ENV dan putenv().
 * 
 * Format .env yang didukung:
 *   KEY=value          → nilai biasa
 *   KEY="nilai spasi"  → nilai dengan tanda kutip
 *   # komentar         → diabaikan
 *   KEY=               → nilai kosong / string kosong
 * =====================================================
 */

function loadEnv(string $path): void {
    // Jika file .env tidak ada, periksa apakah variabel lingkungan krusial sudah didefinisikan (misalnya di Railway/hosting)
    if (!file_exists($path)) {
        if (getenv('DB_HOST') !== false || isset($_ENV['DB_HOST']) || isset($_SERVER['DB_HOST'])) {
            return;
        }

        die('<div style="font-family:Arial;padding:24px;color:#ef4444;background:#1a0a0a;border:1px solid #ef4444;border-radius:8px;margin:20px">
            <h3>❌ File .env tidak ditemukan!</h3>
            <p>Buat file <code>.env</code> di root project (<code>' . dirname($path) . '</code>).</p>
            <p>Salin dari <code>.env.example</code> dan sesuaikan isinya.</p>
        </div>');
    }

    // Baca semua baris dari .env
    $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($lines as $line) {
        $line = trim($line);

        // Lewati baris komentar (diawali #) dan baris kosong
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        // Pisahkan KEY dan VALUE pada tanda = pertama
        if (!str_contains($line, '=')) {
            continue; // Lewati baris tidak valid
        }

        [$key, $value] = explode('=', $line, 2);
        $key   = trim($key);
        $value = trim($value);

        // Hapus tanda kutip " atau ' dari value jika ada
        if (
            (str_starts_with($value, '"') && str_ends_with($value, '"')) ||
            (str_starts_with($value, "'") && str_ends_with($value, "'"))
        ) {
            $value = substr($value, 1, -1);
        }

        // Simpan ke $_ENV, $_SERVER, dan putenv()
        // Hanya set jika belum ada (tidak override env sistem)
        if (!array_key_exists($key, $_ENV)) {
            $_ENV[$key]    = $value;
            $_SERVER[$key] = $value;
            putenv("$key=$value");
        }
    }
}

/**
 * Helper: Ambil nilai dari env dengan nilai default jika tidak ada
 * Penggunaan: env('DB_HOST', 'localhost')
 */
function env(string $key, mixed $default = null): mixed {
    $value = $_ENV[$key] ?? getenv($key);

    if ($value === false || $value === null) {
        return $default;
    }

    // Konversi string boolean
    return match (strtolower((string)$value)) {
        'true', '(true)'   => true,
        'false', '(false)' => false,
        'null', '(null)'   => null,
        'empty', '(empty)' => '',
        default            => $value,
    };
}
