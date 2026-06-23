<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Diagnosa - SiPaGi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<?php
/**
 * HALAMAN HASIL DIAGNOSA
 * ============================================================
 * Halaman ini menerima POST data dari konsultasi.php,
 * kemudian menjalankan engine Forward Chaining untuk mendapatkan
 * hasil diagnosa, dan menampilkannya kepada pasien.
 * 
 * Alur:
 * 1. Validasi data POST (nama & gejala)
 * 2. Jalankan ForwardChaining::diagnosa()
 * 3. Simpan hasil ke database
 * 4. Tampilkan hasil dengan animasi
 * ============================================================
 */
require_once '../config/database.php';
require_once '../config/session.php';
require_once '../forward_chaining.php'; // Load engine FC

// Deteksi apakah user sedang login sebagai admin
$isAdmin = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';

// Validasi: harus dari POST konsultasi
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(BASE_URL . '/pages/konsultasi.php');
}

$namaPasien   = clean($_POST['nama_pasien'] ?? '');
$gejalaDipilih = array_map('intval', $_POST['gejala'] ?? []);

if (empty($namaPasien) || empty($gejalaDipilih)) {
    setFlash('danger', 'Data tidak lengkap. Silakan ulangi konsultasi.');
    redirect(BASE_URL . '/pages/konsultasi.php');
}

// =====================================================
// JALANKAN ENGINE FORWARD CHAINING
// =====================================================
$fc = new ForwardChaining($conn, $gejalaDipilih);
$hasilList = $fc->diagnosa(); // Ini menjalankan proses inferensi

// Ambil diagnosa utama (persentase tertinggi)
$hasilUtama = !empty($hasilList) ? $hasilList[0] : null;

// =====================================================
// SIMPAN HASIL KE DATABASE (RIWAYAT KONSULTASI)
// =====================================================
$diagnosaNama = $hasilUtama ? $hasilUtama['penyakit']['nama'] : 'Tidak Terdeteksi';
$persentase   = $hasilUtama ? $hasilUtama['persentase'] : 0;
$userId       = $_SESSION['user_id'] ?? null;

// Insert ke tabel konsultasi
$stmt = $conn->prepare("INSERT INTO konsultasi (user_id, nama_pasien, hasil_diagnosa, persentase) VALUES (?, ?, ?, ?)");
$stmt->bind_param('issd', $userId, $namaPasien, $diagnosaNama, $persentase);
$stmt->execute();
$konsultasiId = $conn->insert_id; // ID konsultasi yang baru dibuat

// Simpan gejala yang dipilih ke konsultasi_gejala
foreach ($gejalaDipilih as $gejalaId) {
    $stmtG = $conn->prepare("INSERT INTO konsultasi_gejala (konsultasi_id, gejala_id) VALUES (?, ?)");
    $stmtG->bind_param('ii', $konsultasiId, $gejalaId);
    $stmtG->execute();
}

// =====================================================
// Ambil data gejala yang dipilih (untuk ditampilkan)
// =====================================================
$placeholders = implode(',', array_fill(0, count($gejalaDipilih), '?'));
$stmtGejala = $conn->prepare("SELECT * FROM gejala WHERE id IN ($placeholders) ORDER BY kode");
$stmtGejala->bind_param(str_repeat('i', count($gejalaDipilih)), ...$gejalaDipilih);
$stmtGejala->execute();
$gejalaYgDipilih = $stmtGejala->get_result()->fetch_all(MYSQLI_ASSOC);

// Fungsi helper: warna badge berdasarkan persentase
function getPersentaseBadge($pct) {
    if ($pct >= 80) return ['success', '🟢'];
    if ($pct >= 60) return ['warning', '🟡'];
    if ($pct >= 40) return ['info', '🔵'];
    return ['danger', '🔴'];
}
?>

<?php if ($isAdmin): ?>
<!-- LAYOUT ADMIN dengan Sidebar -->
<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="topbar no-print">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="sidebarToggle" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:20px"
                    onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
                <div class="topbar-title">📊 Hasil Diagnosa</div>
            </div>
            <div class="topbar-actions">
                <button onclick="printHasil()" class="btn btn-outline btn-sm">🖨️ Cetak</button>
                <a href="konsultasi.php" class="btn btn-primary btn-sm">🔄 Konsultasi Lagi</a>
            </div>
        </div>
        <div class="page-content">
            <div class="breadcrumb no-print">
                <a href="../admin/index.php">Dashboard</a>
                <span class="sep">›</span>
                <a href="konsultasi.php">Konsultasi</a>
                <span class="sep">›</span>
                <span>Hasil Diagnosa</span>
            </div>
<?php else: ?>
<!-- LAYOUT PUBLIK tanpa Sidebar -->
<nav style="position:sticky;top:0;z-index:100;background:rgba(10,15,30,0.95);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.06);padding:0 28px;height:64px;display:flex;align-items:center;justify-content:space-between" class="no-print">
    <a href="../index.php" style="display:flex;align-items:center;gap:8px;color:inherit">
        <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:8px;display:flex;align-items:center;justify-content:center">🦷</div>
        <span style="font-family:'Poppins',sans-serif;font-weight:700">SiPaGi</span>
    </a>
    <div class="topbar-actions">
        <button onclick="printHasil()" class="btn btn-outline btn-sm">🖨️ Cetak Hasil</button>
        <a href="konsultasi.php" class="btn btn-primary btn-sm">🔄 Konsultasi Lagi</a>
    </div>
</nav>
<div class="main-wrapper" style="max-width:900px;margin:0 auto;padding:28px 20px 60px">
<?php endif; ?>

    <!-- Header Pasien -->
    <div style="display:flex;align-items:center;gap:16px;margin-bottom:28px">
        <div style="width:52px;height:52px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:16px;display:flex;align-items:center;justify-content:center;font-size:22px;box-shadow:0 0 20px rgba(14,165,233,0.3)">
            🦷
        </div>
        <div>
            <h1 style="font-family:'Poppins',sans-serif;font-size:20px;font-weight:700;margin-bottom:4px">
                Hasil Diagnosa: <?= clean($namaPasien) ?>
            </h1>
            <p style="font-size:13px;color:#64748b">
                📅 <?= date('d F Y, H:i') ?> WIB • 
                <?= count($gejalaDipilih) ?> gejala dipilih • 
                ID Konsultasi: #<?= $konsultasiId ?>
            </p>
        </div>
    </div>

    <?php if (empty($hasilList)): ?>
    <!-- =====================================================
         TIDAK ADA DIAGNOSA
         ===================================================== -->
    <div style="text-align:center;padding:60px 20px;background:rgba(255,255,255,0.02);border:1px solid rgba(255,255,255,0.06);border-radius:20px;margin-bottom:24px">
        <div style="font-size:64px;margin-bottom:16px">🤔</div>
        <h2 style="font-family:'Poppins',sans-serif;font-size:22px;margin-bottom:12px">Penyakit Tidak Terdeteksi</h2>
        <p style="color:#64748b;max-width:400px;margin:0 auto;line-height:1.7">
            Gejala yang Anda pilih tidak cocok dengan basis pengetahuan yang tersedia. 
            Mungkin gejala yang dirasakan merupakan kombinasi yang belum terdaftar, 
            atau perlu pemeriksaan langsung oleh dokter gigi.
        </p>
        <a href="konsultasi.php" class="btn btn-primary" style="margin-top:24px">🔄 Ulangi Konsultasi</a>
    </div>

    <?php else: ?>
    <!-- =====================================================
         HASIL DIAGNOSA UTAMA
         ===================================================== -->
    <div class="hasil-hero">
        <span class="hasil-icon">🦷</span>
        <div style="font-size:14px;color:#64748b;margin-bottom:8px">Diagnosa Utama</div>
        <div class="hasil-penyakit"><?= clean($hasilUtama['penyakit']['nama']) ?></div>
        <div style="font-size:14px;color:#94a3b8;margin-top:8px">
            Kode: <strong style="color:#38bdf8"><?= clean($hasilUtama['penyakit']['kode']) ?></strong>
        </div>

        <!-- Persentase -->
        <div style="margin-top:20px;max-width:300px;margin-left:auto;margin-right:auto">
            <div style="display:flex;justify-content:space-between;font-size:12px;margin-bottom:6px">
                <span style="color:#64748b">Tingkat Kecocokan</span>
                <span style="font-weight:700;color:#38bdf8;font-size:16px"><?= $hasilUtama['persentase'] ?>%</span>
            </div>
            <div class="persentase-bar">
                <div class="persentase-fill" data-width="<?= $hasilUtama['persentase'] ?>" style="width:0%"></div>
            </div>
            <div style="font-size:12px;color:#64748b;margin-top:6px">
                <?= $hasilUtama['jumlah_cocok'] ?> dari <?= $hasilUtama['total_gejala'] ?> gejala cocok
            </div>
        </div>
    </div>

    <!-- DESKRIPSI & SOLUSI -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:24px">
        <div class="card">
            <div class="card-header">
                <div class="card-title">📖 Deskripsi Penyakit</div>
            </div>
            <div class="card-body">
                <p style="font-size:14px;color:#94a3b8;line-height:1.8">
                    <?= nl2br(clean($hasilUtama['penyakit']['deskripsi'])) ?>
                </p>
            </div>
        </div>
        <div class="card">
            <div class="card-header">
                <div class="card-title" style="color:#10b981">💊 Rekomendasi Penanganan</div>
            </div>
            <div class="card-body">
                <p style="font-size:14px;color:#94a3b8;line-height:1.8">
                    <?= nl2br(clean($hasilUtama['penyakit']['solusi'])) ?>
                </p>
            </div>
        </div>
    </div>

    <!-- GEJALA YANG DIPILIH -->
    <div class="card" style="margin-bottom:24px">
        <div class="card-header">
            <div class="card-title">✅ Gejala yang Anda Pilih (<?= count($gejalaDipilih) ?> gejala)</div>
        </div>
        <div class="card-body">
            <div class="gejala-tags">
                <?php foreach ($gejalaYgDipilih as $g): ?>
                <div style="display:inline-flex;align-items:center;gap:6px;background:rgba(14,165,233,0.08);border:1px solid rgba(14,165,233,0.2);border-radius:20px;padding:5px 12px;margin:3px">
                    <span style="font-size:10px;font-weight:700;color:#38bdf8"><?= clean($g['kode']) ?></span>
                    <span style="font-size:12px;color:#94a3b8"><?= clean($g['nama']) ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- =====================================================
         SEMUA KEMUNGKINAN DIAGNOSA
         ===================================================== -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">📊 Analisis Semua Kemungkinan Penyakit</div>
            <span class="badge badge-info"><?= count($hasilList) ?> kemungkinan</span>
        </div>
        <div class="card-body" style="padding:0">
            <div class="hasil-penyakit-list" style="padding:16px">
                <?php foreach ($hasilList as $idx => $hasil): ?>
                <?php [$badgeType, $icon] = getPersentaseBadge($hasil['persentase']); ?>
                <div class="hasil-item">
                    <div class="hasil-item-header" onclick="toggleDetail(this)">
                        <div class="hasil-item-name">
                            <?php if ($idx === 0): ?>
                            <span style="background:linear-gradient(135deg,#f59e0b,#ef4444);padding:3px 10px;border-radius:20px;font-size:11px;color:white">🏆 UTAMA</span>
                            <?php else: ?>
                            <span style="background:rgba(255,255,255,0.06);padding:3px 10px;border-radius:20px;font-size:11px;color:#64748b">#<?= $idx+1 ?></span>
                            <?php endif; ?>
                            <span class="badge badge-primary" style="font-size:11px"><?= clean($hasil['penyakit']['kode']) ?></span>
                            <?= clean($hasil['penyakit']['nama']) ?>
                        </div>
                        <div style="display:flex;align-items:center;gap:12px">
                            <!-- Progress Circle SVG -->
                            <div class="progress-circle">
                                <svg viewBox="0 0 70 70" width="60" height="60">
                                    <circle class="bg" cx="35" cy="35" r="30"/>
                                    <circle class="fill" cx="35" cy="35" r="30" data-pct="<?= $hasil['persentase'] ?>"/>
                                </svg>
                                <div class="text" style="font-size:11px;font-weight:700;color:#38bdf8"><?= $hasil['persentase'] ?>%</div>
                            </div>
                            <span class="accordion-arrow" style="color:#64748b;transition:transform 0.3s;font-size:18px">▼</span>
                        </div>
                    </div>

                    <div class="hasil-item-body">
                        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-top:4px">
                            <!-- Gejala Cocok -->
                            <div>
                                <div style="font-size:12px;font-weight:600;color:#10b981;margin-bottom:8px">
                                    ✅ Gejala Cocok (<?= count($hasil['gejala_cocok']) ?>)
                                </div>
                                <div class="gejala-tags">
                                    <?php foreach ($hasil['gejala_cocok'] as $g): ?>
                                    <span class="gejala-tag cocok">
                                        <?= clean($g['kode']) ?> - <?= clean($g['nama']) ?>
                                    </span>
                                    <?php endforeach; ?>
                                    <?php if (empty($hasil['gejala_cocok'])): ?>
                                    <span style="color:#64748b;font-size:12px">Tidak ada</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <!-- Gejala Tidak Cocok -->
                            <div>
                                <div style="font-size:12px;font-weight:600;color:#ef4444;margin-bottom:8px">
                                    ❌ Gejala Belum Ada (<?= count($hasil['gejala_kurang']) ?>)
                                </div>
                                <div class="gejala-tags">
                                    <?php foreach ($hasil['gejala_kurang'] as $g): ?>
                                    <span class="gejala-tag kurang">
                                        <?= clean($g['kode']) ?> - <?= clean($g['nama']) ?>
                                    </span>
                                    <?php endforeach; ?>
                                    <?php if (empty($hasil['gejala_kurang'])): ?>
                                    <span style="color:#10b981;font-size:12px">Semua gejala cocok! 🎯</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <!-- Penjelasan Singkat -->
                        <?php if ($hasil['penyakit']['deskripsi']): ?>
                        <div style="margin-top:16px;padding:12px;background:rgba(255,255,255,0.02);border-radius:8px;font-size:13px;color:#64748b;line-height:1.6;border-left:3px solid rgba(14,165,233,0.3)">
                            <?= clean(substr($hasil['penyakit']['deskripsi'], 0, 200)) ?>...
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- DISCLAIMER -->
    <div style="margin-top:24px;padding:16px 20px;background:rgba(245,158,11,0.06);border:1px solid rgba(245,158,11,0.2);border-radius:12px;font-size:13px;color:#fcd34d">
        ⚠️ <strong>Disclaimer:</strong> Hasil diagnosa ini bersifat sementara dan hanya sebagai panduan awal. 
        Untuk diagnosa yang akurat dan penanganan yang tepat, <strong>segera konsultasikan ke dokter gigi</strong> 
        Praktik Mandiri Drg. Hj. Rini Sutarti.
    </div>

    <!-- Tombol Aksi -->
    <div style="display:flex;gap:12px;margin-top:24px;flex-wrap:wrap" class="no-print">
        <a href="konsultasi.php" class="btn btn-outline">🔄 Konsultasi Lagi</a>
        <button onclick="printHasil()" class="btn btn-success">🖨️ Cetak Hasil</button>
        <a href="riwayat.php" class="btn btn-outline">📋 Lihat Riwayat</a>
    </div>

<?php if ($isAdmin): ?>
        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div><!-- /app-wrapper -->
<?php else: ?>
</div><!-- /main-wrapper publik -->
<?php endif; ?>

<script src="../assets/js/main.js"></script>
<script>
// Toggle detail panel hasil
function toggleDetail(header) {
    const body = header.nextElementSibling;
    const arrow = header.querySelector('.accordion-arrow');
    body.classList.toggle('open');
    if (arrow) {
        arrow.style.transform = body.classList.contains('open') ? 'rotate(180deg)' : 'rotate(0)';
    }
}

// Buka item pertama otomatis
document.addEventListener('DOMContentLoaded', function() {
    const firstHeader = document.querySelector('.hasil-item-header');
    if (firstHeader) toggleDetail(firstHeader);

    // Animasi progress bars & circles
    animateProgressBars();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
