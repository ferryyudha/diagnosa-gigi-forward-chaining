<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Konsultasi penyakit gigi online - pilih gejala dan dapatkan diagnosa menggunakan sistem pakar Forward Chaining">
    <title>Konsultasi Pasien - SiPaGi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
    <style>
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 4px 0 20px;
        }
        .step { display: flex; align-items: center; gap: 8px; }
        .step-num {
            width: 28px; height: 28px;
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: 12px; font-weight: 700;
            border: 2px solid var(--border);
            color: var(--text-muted);
        }
        .step.active .step-num {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
        }
        .step-label { font-size: 13px; font-weight: 500; color: var(--text-muted); }
        .step.active .step-label { color: var(--text-primary); }
        .step-sep { width: 40px; height: 1px; background: var(--border); }

        /* Navbar publik (hanya tampil saat belum login) */
        .konsultasi-nav {
            position: sticky; top: 0; z-index: 100;
            background: rgba(10,15,30,0.95);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid var(--border);
            padding: 0 28px;
            height: 64px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
    </style>
</head>
<body>
<?php
/**
 * HALAMAN KONSULTASI PASIEN
 * ============================================================
 * Menampilkan form konsultasi untuk memilih gejala.
 * 
 * Logika tampilan:
 * - Jika user LOGIN sebagai admin → pakai layout admin (dengan sidebar)
 * - Jika user BELUM login → pakai layout publik (dengan navbar biasa)
 * ============================================================
 */
require_once '../config/database.php';
require_once '../config/session.php';

// Deteksi apakah user sedang login sebagai admin
$isAdmin = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';

// Ambil semua gejala dari database
$gejalaQuery = $conn->query("SELECT * FROM gejala ORDER BY kode");
$gejalaList  = [];
while ($g = $gejalaQuery->fetch_assoc()) {
    $gejalaList[] = $g;
}
?>

<?php if ($isAdmin): ?>
<!-- =====================================================
     LAYOUT ADMIN — dengan Sidebar
     ===================================================== -->
<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- Topbar Admin -->
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="sidebarToggle"
                    style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:20px"
                    onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
                <div class="topbar-title">🔍 Konsultasi Pasien</div>
            </div>
            <div class="topbar-actions">
                <span style="font-size:13px;color:#64748b"><?= date('d F Y') ?></span>
            </div>
        </div>

        <div class="page-content">
            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <a href="../admin/index.php">Dashboard</a>
                <span class="sep">›</span>
                <span>Konsultasi Pasien</span>
            </div>

<?php else: ?>
<!-- =====================================================
     LAYOUT PUBLIK — tanpa Sidebar
     ===================================================== -->
<nav class="konsultasi-nav">
    <div style="display:flex;align-items:center;gap:12px">
        <a href="../index.php" style="display:flex;align-items:center;gap:8px;color:inherit">
            <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:8px;display:flex;align-items:center;justify-content:center">🦷</div>
            <span style="font-family:'Poppins',sans-serif;font-weight:700">SiPaGi</span>
        </a>
    </div>
    <div style="font-size:13px;color:#64748b">
        Praktik Mandiri <span style="color:#94a3b8;font-weight:500">Drg. Hj. Rini Sutarti</span>
    </div>
    <a href="../auth/login.php" class="btn btn-outline btn-sm">🔐 Login Admin</a>
</nav>

<div style="max-width:900px;margin:0 auto;padding:24px 20px 60px">
<?php endif; ?>

    <!-- Step Indicator -->
    <div class="step-indicator">
        <div class="step active">
            <div class="step-num">1</div>
            <div class="step-label">Isi Data Diri</div>
        </div>
        <div class="step-sep"></div>
        <div class="step active">
            <div class="step-num">2</div>
            <div class="step-label">Pilih Gejala</div>
        </div>
        <div class="step-sep"></div>
        <div class="step">
            <div class="step-num">3</div>
            <div class="step-label">Hasil Diagnosa</div>
        </div>
    </div>

    <!-- Header -->
    <div style="margin-bottom:28px">
        <h1 style="font-family:'Poppins',sans-serif;font-size:24px;font-weight:700;margin-bottom:8px">
            🔍 Konsultasi Penyakit Gigi
        </h1>
        <p style="color:#64748b;font-size:14px">
            Pilih semua gejala yang dirasakan pasien. Semakin lengkap gejala yang dipilih, semakin akurat hasil diagnosa.
        </p>
    </div>

    <!-- =====================================================
         FORM KONSULTASI
         ===================================================== -->
    <form method="POST" action="hasil.php" id="formKonsultasi">

        <!-- STEP 1: Data Pasien -->
        <div class="card" style="margin-bottom:20px">
            <div class="card-header">
                <div class="card-title">👤 Data Pasien</div>
            </div>
            <div class="card-body">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Nama Lengkap *</label>
                        <input type="text" name="nama_pasien" id="inputNama" class="form-control"
                               placeholder="Masukkan nama pasien..." required>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Usia</label>
                        <input type="number" name="usia" class="form-control"
                               placeholder="Usia (tahun)" min="1" max="120">
                    </div>
                </div>
            </div>
        </div>

        <!-- STEP 2: Pilih Gejala -->
        <div class="card" style="margin-bottom:24px">
            <div class="card-header">
                <div class="card-title">📋 Pilih Gejala yang Dirasakan</div>
                <div style="display:flex;align-items:center;gap:10px">
                    <span style="font-size:13px;color:#64748b">
                        Dipilih: <strong id="selectedCount" style="color:#38bdf8">0</strong> gejala
                    </span>
                    <button type="button" onclick="resetGejala()" class="btn btn-outline btn-sm">↺ Reset</button>
                </div>
            </div>
            <div class="card-body">
                <!-- Info -->
                <div style="padding:12px 16px;background:rgba(14,165,233,0.06);border-radius:10px;margin-bottom:20px;font-size:13px;color:#7dd3fc;border:1px solid rgba(14,165,233,0.15)">
                    <strong>ℹ️ Petunjuk:</strong> Centang semua gejala yang saat ini dirasakan pada gigi atau mulut.
                    Jangan mencentang gejala yang tidak dirasakan.
                </div>

                <!-- Grid Gejala -->
                <div class="gejala-grid">
                    <?php foreach ($gejalaList as $g): ?>
                    <div class="gejala-item">
                        <input type="checkbox"
                               class="gejala-checkbox"
                               name="gejala[]"
                               value="<?= $g['id'] ?>"
                               id="gejala_<?= $g['id'] ?>"
                               onchange="updateSelectedCount()">
                        <label class="gejala-label" for="gejala_<?= $g['id'] ?>">
                            <div class="gejala-checkmark">✓</div>
                            <span class="gejala-code"><?= clean($g['kode']) ?></span>
                            <span><?= clean($g['nama']) ?></span>
                        </label>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Submit -->
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px">
            <div style="font-size:13px;color:#64748b">
                ⚠️ Sistem ini hanya sebagai alat bantu diagnosa awal.<br>
                Tetap konsultasikan ke dokter gigi untuk penanganan lebih lanjut.
            </div>
            <button type="submit" id="btnDiagnosa" class="btn btn-primary btn-lg"
                    disabled style="opacity:0.5;min-width:200px">
                <span id="btnText">🔍 Mulai Diagnosa</span>
                <span id="btnLoader" style="display:none"><span class="loader"></span></span>
            </button>
        </div>

    </form>

<?php if ($isAdmin): ?>
        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div><!-- /app-wrapper -->
<?php else: ?>
</div><!-- /public wrapper -->
<?php endif; ?>

<script src="../assets/js/main.js"></script>
<script>
function updateSelectedCount() {
    const count = document.querySelectorAll('.gejala-checkbox:checked').length;
    document.getElementById('selectedCount').textContent = count;
    const btn = document.getElementById('btnDiagnosa');
    btn.disabled = count === 0;
    btn.style.opacity = count === 0 ? '0.5' : '1';
}

function resetGejala() {
    document.querySelectorAll('.gejala-checkbox').forEach(cb => cb.checked = false);
    updateSelectedCount();
}

document.getElementById('formKonsultasi').addEventListener('submit', function(e) {
    const nama = document.getElementById('inputNama').value.trim();
    const checked = document.querySelectorAll('.gejala-checkbox:checked').length;

    if (!nama) { e.preventDefault(); alert('Nama pasien harus diisi!'); return; }
    if (checked === 0) { e.preventDefault(); alert('Pilih minimal 1 gejala!'); return; }

    document.getElementById('btnText').style.display = 'none';
    document.getElementById('btnLoader').style.display = 'inline-flex';
    document.getElementById('btnDiagnosa').disabled = true;
});
</script>
</body>
</html>
