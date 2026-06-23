<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Konsultasi - SiPaGi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<?php
/**
 * HALAMAN RIWAYAT KONSULTASI
 * Menampilkan semua histori konsultasi yang pernah dilakukan.
 * Admin dapat melihat detail setiap konsultasi.
 */
require_once '../config/database.php';
require_once '../config/session.php';

// Cek apakah mode admin atau publik
$isAdmin = isset($_SESSION['user_id']) && $_SESSION['role'] === 'admin';

// Filter berdasarkan pencarian
$search = clean($_GET['search'] ?? '');
$where = '';
if ($search) {
    $searchEsc = $conn->real_escape_string($search);
    $where = "WHERE k.nama_pasien LIKE '%$searchEsc%' OR k.hasil_diagnosa LIKE '%$searchEsc%'";
}

// Hapus riwayat (admin only)
if ($isAdmin && isset($_GET['hapus'])) {
    $hapusId = (int)$_GET['hapus'];
    $conn->query("DELETE FROM konsultasi WHERE id = $hapusId");
    setFlash('success', 'Riwayat konsultasi berhasil dihapus!');
    redirect(BASE_URL . '/pages/riwayat.php');
}

$flash = getFlash();

// Ambil semua riwayat
$query = "SELECT k.*, u.nama as nama_user 
          FROM konsultasi k 
          LEFT JOIN users u ON k.user_id = u.id 
          $where
          ORDER BY k.tanggal DESC";
$riwayat = $conn->query($query);

// Statistik ringkas
$totalQuery = $conn->query("SELECT COUNT(*) as total FROM konsultasi");
$total = $totalQuery->fetch_assoc()['total'];
?>

<!-- NAVBAR -->
<nav style="position:sticky;top:0;z-index:100;background:rgba(10,15,30,0.95);backdrop-filter:blur(20px);border-bottom:1px solid rgba(255,255,255,0.06);padding:0 28px;height:64px;display:flex;align-items:center;justify-content:space-between">
    <?php if ($isAdmin): ?>
    <a href="../admin/index.php" style="display:flex;align-items:center;gap:8px;color:inherit">
        <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:8px;display:flex;align-items:center;justify-content:center">🦷</div>
        <span style="font-family:'Poppins',sans-serif;font-weight:700">SiPaGi Admin</span>
    </a>
    <?php else: ?>
    <a href="../index.php" style="display:flex;align-items:center;gap:8px;color:inherit">
        <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:8px;display:flex;align-items:center;justify-content:center">🦷</div>
        <span style="font-family:'Poppins',sans-serif;font-weight:700">SiPaGi</span>
    </a>
    <?php endif; ?>

    <div style="font-size:20px;font-weight:600;font-family:'Poppins',sans-serif">
        📋 Riwayat Konsultasi
    </div>

    <a href="konsultasi.php" class="btn btn-primary btn-sm">+ Konsultasi Baru</a>
</nav>

<?php if ($isAdmin): ?>
<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>
    <div class="main-content">
        <div class="page-content">
<?php else: ?>
<div style="max-width:1100px;margin:0 auto;padding:28px 20px 60px">
<?php endif; ?>

    <?php if ($flash): ?>
    <div class="alert alert-<?= $flash['type'] ?>">
        <?= $flash['type'] === 'success' ? '✅' : '❌' ?> <?= clean($flash['message']) ?>
    </div>
    <?php endif; ?>

    <!-- Header & Filter -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:16px">
        <div>
            <h1 style="font-family:'Poppins',sans-serif;font-size:20px;font-weight:700;margin-bottom:4px">
                📋 Riwayat Konsultasi
            </h1>
            <p style="font-size:13px;color:#64748b">Total: <?= $total ?> konsultasi tersimpan</p>
        </div>
        <!-- Search -->
        <form method="GET">
            <div class="search-box">
                <span class="search-icon">🔍</span>
                <input type="text" name="search" placeholder="Cari nama / penyakit..." value="<?= clean($search) ?>">
                <button type="submit" style="background:none;border:none;color:#0ea5e9;cursor:pointer;font-size:13px">Cari</button>
            </div>
        </form>
    </div>

    <!-- Tabel Riwayat -->
    <div class="card">
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama Pasien</th>
                        <th>Hasil Diagnosa</th>
                        <th>Kecocokan</th>
                        <th>Tanggal & Waktu</th>
                        <th style="text-align:center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($riwayat->num_rows === 0): ?>
                    <tr>
                        <td colspan="6" style="text-align:center;padding:50px 20px">
                            <div style="font-size:48px;margin-bottom:12px">📋</div>
                            <div style="color:#64748b">
                                <?= $search ? "Tidak ada hasil untuk \"$search\"" : 'Belum ada riwayat konsultasi' ?>
                            </div>
                            <?php if ($search): ?>
                            <a href="riwayat.php" style="display:inline-block;margin-top:12px;font-size:13px;color:#0ea5e9">← Tampilkan semua</a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php $no = 1; while ($row = $riwayat->fetch_assoc()): ?>
                    <tr>
                        <td style="color:#64748b"><?= $no++ ?></td>
                        <td>
                            <div style="display:flex;align-items:center;gap:10px">
                                <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:700;flex-shrink:0">
                                    <?= strtoupper(substr($row['nama_pasien'], 0, 1)) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600"><?= clean($row['nama_pasien']) ?></div>
                                    <div style="font-size:11px;color:#64748b">ID #<?= $row['id'] ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($row['hasil_diagnosa'] && $row['hasil_diagnosa'] !== 'Tidak Terdeteksi'): ?>
                            <span class="badge badge-primary"><?= clean($row['hasil_diagnosa']) ?></span>
                            <?php else: ?>
                            <span class="badge badge-warning">Tidak Terdeteksi</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div style="display:flex;align-items:center;gap:8px">
                                <div style="width:60px;height:6px;background:rgba(255,255,255,0.06);border-radius:3px">
                                    <div style="height:100%;width:<?= $row['persentase'] ?>%;background:linear-gradient(90deg,#0ea5e9,#06b6d4);border-radius:3px"></div>
                                </div>
                                <span style="font-size:12px;font-weight:700;color:#38bdf8"><?= $row['persentase'] ?>%</span>
                            </div>
                        </td>
                        <td style="font-size:12px;color:#64748b">
                            <div><?= date('d M Y', strtotime($row['tanggal'])) ?></div>
                            <div><?= date('H:i', strtotime($row['tanggal'])) ?> WIB</div>
                        </td>
                        <td style="text-align:center">
                            <div style="display:flex;gap:6px;justify-content:center">
                                <a href="detail_konsultasi.php?id=<?= $row['id'] ?>" 
                                   class="btn btn-outline btn-sm">👁 Detail</a>
                                <?php if ($isAdmin): ?>
                                <a href="?hapus=<?= $row['id'] ?>" 
                                   onclick="return confirm('Hapus riwayat ini?')"
                                   class="btn btn-danger btn-sm">🗑</a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

<?php if ($isAdmin): ?>
        </div>
    </div>
</div>
<?php else: ?>
</div>
<?php endif; ?>

<script src="../assets/js/main.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
