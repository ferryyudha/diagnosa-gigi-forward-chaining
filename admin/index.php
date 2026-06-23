<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - SiPaGi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
    <!-- Chart.js untuk grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
</head>
<body>
<?php
/**
 * DASHBOARD ADMIN
 * Halaman utama panel admin yang menampilkan statistik sistem:
 * - Total data penyakit, gejala, aturan, konsultasi
 * - Grafik diagnosa terbanyak
 * - Riwayat konsultasi terbaru
 */
require_once '../config/database.php'; // Load .env & koneksi DB terlebih dahulu
require_once '../config/session.php';  // Session butuh BASE_URL dari database.php
requireAdmin(); // Hanya admin yang bisa akses

// =====================================================
// AMBIL STATISTIK DARI DATABASE
// =====================================================

// Hitung total penyakit
$totalPenyakit = $conn->query("SELECT COUNT(*) as total FROM penyakit")->fetch_assoc()['total'];

// Hitung total gejala
$totalGejala = $conn->query("SELECT COUNT(*) as total FROM gejala")->fetch_assoc()['total'];

// Hitung total aturan (relasi penyakit-gejala)
$totalAturan = $conn->query("SELECT COUNT(*) as total FROM aturan")->fetch_assoc()['total'];

// Hitung total konsultasi yang sudah dilakukan
$totalKonsultasi = $conn->query("SELECT COUNT(*) as total FROM konsultasi")->fetch_assoc()['total'];

// Konsultasi bulan ini
$konsultasiBulanIni = $conn->query("SELECT COUNT(*) as total FROM konsultasi WHERE MONTH(tanggal) = MONTH(NOW()) AND YEAR(tanggal) = YEAR(NOW())")->fetch_assoc()['total'];

// =====================================================
// DATA UNTUK GRAFIK - Penyakit yang paling sering terdiagnosa
// =====================================================
$queryGrafik = "SELECT p.nama, COUNT(k.id) as jumlah 
                FROM konsultasi k 
                JOIN penyakit p ON k.hasil_diagnosa = p.nama 
                GROUP BY p.nama 
                ORDER BY jumlah DESC 
                LIMIT 8";
$resultGrafik = $conn->query($queryGrafik);
$labelGrafik = [];
$dataGrafik = [];
while ($row = $resultGrafik->fetch_assoc()) {
    $labelGrafik[] = $row['nama'];
    $dataGrafik[] = $row['jumlah'];
}

// =====================================================
// KONSULTASI TERBARU (10 data terakhir)
// =====================================================
$queryRiwayat = "SELECT k.*, u.nama as nama_user 
                 FROM konsultasi k 
                 LEFT JOIN users u ON k.user_id = u.id 
                 ORDER BY k.tanggal DESC 
                 LIMIT 10";
$riwayatTerbaru = $conn->query($queryRiwayat);

$flash = getFlash();
?>

<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <!-- TOPBAR -->
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="sidebarToggle" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:20px;display:none" onclick="document.querySelector('.sidebar').classList.toggle('open')">☰</button>
                <div class="topbar-title">📊 Dashboard</div>
            </div>
            <div class="topbar-actions">
                <span style="font-size:13px;color:#64748b">
                    <?= date('d F Y') ?>
                </span>
                <a href="../pages/konsultasi.php" class="btn btn-primary btn-sm">
                    + Konsultasi Baru
                </a>
            </div>
        </div>

        <div class="page-content">
            <!-- Flash Message -->
            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['type'] === 'success' ? '✅' : '❌' ?>
                <?= clean($flash['message']) ?>
            </div>
            <?php endif; ?>

            <!-- Breadcrumb -->
            <div class="breadcrumb">
                <span>🏠 Beranda</span>
                <span class="sep">›</span>
                <span>Dashboard</span>
            </div>

            <!-- =====================================================
                 STAT CARDS - Ringkasan Data
                 ===================================================== -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon blue">🦠</div>
                    <div>
                        <div class="stat-value" data-count="<?= $totalPenyakit ?>"><?= $totalPenyakit ?></div>
                        <div class="stat-label">Total Penyakit</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon green">📝</div>
                    <div>
                        <div class="stat-value" data-count="<?= $totalGejala ?>"><?= $totalGejala ?></div>
                        <div class="stat-label">Total Gejala</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon yellow">⚙️</div>
                    <div>
                        <div class="stat-value" data-count="<?= $totalAturan ?>"><?= $totalAturan ?></div>
                        <div class="stat-label">Total Aturan</div>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon red">📋</div>
                    <div>
                        <div class="stat-value" data-count="<?= $totalKonsultasi ?>"><?= $totalKonsultasi ?></div>
                        <div class="stat-label">Total Konsultasi</div>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 GRAFIK + TABEL
                 ===================================================== -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;margin-bottom:24px">
                <!-- Grafik Diagnosa -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">📈 Diagnosa Terbanyak</div>
                        <span class="badge badge-info">Chart</span>
                    </div>
                    <div class="card-body">
                        <?php if (empty($dataGrafik)): ?>
                        <div class="empty-state" style="padding:30px 0">
                            <div class="icon">📊</div>
                            <p>Belum ada data konsultasi</p>
                        </div>
                        <?php else: ?>
                        <canvas id="chartDiagnosa" height="220"></canvas>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-title">⚡ Ringkasan Sistem</div>
                    </div>
                    <div class="card-body">
                        <div style="display:flex;flex-direction:column;gap:16px">
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;background:rgba(14,165,233,0.08);border-radius:10px;border:1px solid rgba(14,165,233,0.15)">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span>📅</span>
                                    <span style="font-size:14px">Konsultasi Bulan Ini</span>
                                </div>
                                <span style="font-weight:700;color:#38bdf8"><?= $konsultasiBulanIni ?></span>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;background:rgba(16,185,129,0.08);border-radius:10px;border:1px solid rgba(16,185,129,0.15)">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span>🧠</span>
                                    <span style="font-size:14px">Metode Inferensi</span>
                                </div>
                                <span style="font-weight:600;color:#6ee7b7;font-size:13px">Forward Chaining</span>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;background:rgba(245,158,11,0.08);border-radius:10px;border:1px solid rgba(245,158,11,0.15)">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span>🏥</span>
                                    <span style="font-size:14px">Nama Klinik</span>
                                </div>
                                <span style="font-weight:600;color:#fcd34d;font-size:12px">Drg. Hj. Rini Sutarti</span>
                            </div>
                            <div style="display:flex;align-items:center;justify-content:space-between;padding:14px;background:rgba(168,85,247,0.08);border-radius:10px;border:1px solid rgba(168,85,247,0.15)">
                                <div style="display:flex;align-items:center;gap:10px">
                                    <span>🔗</span>
                                    <span style="font-size:14px">Status Sistem</span>
                                </div>
                                <span class="badge badge-success">✅ Online</span>
                            </div>
                        </div>

                        <div style="margin-top:20px">
                            <a href="../pages/konsultasi.php" class="btn btn-primary w-100">
                                🔍 Mulai Konsultasi Baru
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- =====================================================
                 TABEL RIWAYAT TERBARU
                 ===================================================== -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">🕐 Konsultasi Terbaru</div>
                    <a href="../pages/riwayat.php" class="btn btn-outline btn-sm">Lihat Semua →</a>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Pasien</th>
                                <th>Hasil Diagnosa</th>
                                <th>Persentase</th>
                                <th>Tanggal</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($riwayatTerbaru->num_rows === 0): ?>
                            <tr>
                                <td colspan="6" class="text-center" style="padding:40px;color:#64748b">
                                    <div style="font-size:36px;margin-bottom:8px">📋</div>
                                    <div>Belum ada data konsultasi</div>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php $no = 1; while ($row = $riwayatTerbaru->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div style="font-weight:500"><?= clean($row['nama_pasien']) ?></div>
                                </td>
                                <td>
                                    <?php if ($row['hasil_diagnosa']): ?>
                                    <span class="badge badge-primary" style="font-size:11px"><?= clean($row['hasil_diagnosa']) ?></span>
                                    <?php else: ?>
                                    <span class="badge badge-warning">Tidak Terdeteksi</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:8px">
                                        <div style="flex:1;height:6px;background:rgba(255,255,255,0.08);border-radius:3px;max-width:80px">
                                            <div style="height:100%;width:<?= $row['persentase'] ?>%;background:linear-gradient(90deg,#0ea5e9,#06b6d4);border-radius:3px"></div>
                                        </div>
                                        <span style="font-size:12px;font-weight:600;color:#38bdf8"><?= $row['persentase'] ?>%</span>
                                    </div>
                                </td>
                                <td style="color:#64748b;font-size:12px">
                                    <?= date('d M Y H:i', strtotime($row['tanggal'])) ?>
                                </td>
                                <td>
                                    <a href="../pages/detail_konsultasi.php?id=<?= $row['id'] ?>" 
                                       class="btn btn-outline btn-sm">👁 Detail</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /page-content -->
    </div><!-- /main-content -->
</div><!-- /app-wrapper -->

<script src="../assets/js/main.js"></script>
<script>
// =====================================================
// INISIALISASI GRAFIK CHART.JS
// Grafik bar yang menampilkan penyakit paling sering terdiagnosa
// =====================================================
<?php if (!empty($dataGrafik)): ?>
const ctx = document.getElementById('chartDiagnosa').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?= json_encode($labelGrafik) ?>,
        datasets: [{
            label: 'Jumlah Diagnosa',
            data: <?= json_encode($dataGrafik) ?>,
            backgroundColor: [
                'rgba(14, 165, 233, 0.7)',
                'rgba(6, 182, 212, 0.7)',
                'rgba(16, 185, 129, 0.7)',
                'rgba(245, 158, 11, 0.7)',
                'rgba(239, 68, 68, 0.7)',
                'rgba(168, 85, 247, 0.7)',
                'rgba(236, 72, 153, 0.7)',
                'rgba(59, 130, 246, 0.7)',
            ],
            borderColor: 'rgba(14, 165, 233, 0.9)',
            borderWidth: 1,
            borderRadius: 6,
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: '#0d1526',
                borderColor: 'rgba(14,165,233,0.3)',
                borderWidth: 1,
                titleColor: '#f0f6ff',
                bodyColor: '#94a3b8',
            }
        },
        scales: {
            x: {
                ticks: { color: '#64748b', font: { size: 10 } },
                grid: { color: 'rgba(255,255,255,0.04)' }
            },
            y: {
                ticks: { color: '#64748b', stepSize: 1 },
                grid: { color: 'rgba(255,255,255,0.04)' }
            }
        }
    }
});
<?php endif; ?>
</script>
</body>
</html>
