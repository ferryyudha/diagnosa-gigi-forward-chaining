<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gejala - SiPaGi Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<?php
/**
 * HALAMAN KELOLA GEJALA (CRUD)
 * Gejala adalah FAKTA dalam sistem forward chaining.
 * Pasien akan memilih gejala yang dirasakan, kemudian sistem
 * akan mencocokkan dengan basis aturan untuk menghasilkan diagnosa.
 */
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$flash = getFlash();

// =====================================================
// PROSES CRUD GEJALA
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $kode = strtoupper(clean($_POST['kode'] ?? ''));
    $nama = clean($_POST['nama'] ?? '');

    if ($aksi === 'tambah') {
        if (empty($nama)) {
            setFlash('danger', 'Nama gejala tidak boleh kosong!');
        } else {
            $stmt = $conn->prepare("INSERT INTO gejala (kode, nama) VALUES (?, ?)");
            $stmt->bind_param('ss', $kode, $nama);
            $stmt->execute() 
                ? setFlash('success', "Gejala '$nama' berhasil ditambahkan!") 
                : setFlash('danger', 'Gagal menambahkan gejala!');
        }
    } elseif ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE gejala SET kode=?, nama=? WHERE id=?");
        $stmt->bind_param('ssi', $kode, $nama, $id);
        $stmt->execute() 
            ? setFlash('success', 'Gejala berhasil diperbarui!') 
            : setFlash('danger', 'Gagal memperbarui!');
    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM gejala WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute() 
            ? setFlash('success', 'Gejala berhasil dihapus!') 
            : setFlash('danger', 'Gagal menghapus!');
    }

    redirect(BASE_URL . '/admin/gejala.php');
}

// Ambil semua gejala beserta jumlah penyakit yang menggunakannya
$gejalaList = $conn->query("
    SELECT g.*, COUNT(a.id) as dipakai_oleh
    FROM gejala g 
    LEFT JOIN aturan a ON g.id = a.gejala_id 
    GROUP BY g.id 
    ORDER BY g.kode
");
?>

<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="sidebarToggle" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:20px">☰</button>
                <div class="topbar-title">📝 Kelola Data Gejala</div>
            </div>
            <button onclick="openModal('modalTambah')" class="btn btn-primary btn-sm">
                + Tambah Gejala
            </button>
        </div>

        <div class="page-content">
            <div class="breadcrumb">
                <a href="index.php">Dashboard</a>
                <span class="sep">›</span>
                <span>Data Gejala</span>
            </div>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['type'] === 'success' ? '✅' : '❌' ?> <?= clean($flash['message']) ?>
            </div>
            <?php endif; ?>

            <div style="padding:16px 20px;background:rgba(16,185,129,0.06);border:1px solid rgba(16,185,129,0.15);border-radius:12px;margin-bottom:24px;font-size:13px;color:#6ee7b7">
                <strong>💡 Tentang Gejala:</strong> Gejala merupakan <strong>FAKTA (IF)</strong> dalam aturan Forward Chaining. 
                Pasien akan memilih gejala yang dirasakan, dan mesin inferensi akan mencocokkannya dengan aturan yang ada.
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="card-title">📋 Daftar Gejala (<?= $gejalaList->num_rows ?> data)</div>
                    <div class="search-box" style="max-width:250px">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="tableSearch" placeholder="Cari gejala...">
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Gejala</th>
                                <th>Dipakai oleh</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($gejalaList->num_rows === 0): ?>
                            <tr>
                                <td colspan="4" style="text-align:center;padding:40px;color:#64748b">
                                    Belum ada data gejala.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($g = $gejalaList->fetch_assoc()): ?>
                            <tr>
                                <td><span class="badge badge-success"><?= clean($g['kode']) ?></span></td>
                                <td style="font-size:14px"><?= clean($g['nama']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $g['dipakai_oleh'] > 0 ? 'primary' : 'warning' ?>">
                                        <?= $g['dipakai_oleh'] ?> penyakit
                                    </span>
                                </td>
                                <td style="text-align:center">
                                    <div style="display:flex;gap:6px;justify-content:center">
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($g)) ?>)" 
                                                class="btn btn-outline btn-sm">✏️ Edit</button>
                                        <form method="POST" onsubmit="return confirmDelete(this)">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $g['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">➕ Tambah Gejala Baru</div>
            <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 3fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Kode *</label>
                        <input type="text" name="kode" class="form-control" placeholder="G026" maxlength="10" style="text-transform:uppercase" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Nama Gejala *</label>
                        <input type="text" name="nama" class="form-control" placeholder="Deskripsi gejala yang dialami pasien..." required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Gejala</div>
            <button class="modal-close" onclick="closeModal('modalEdit')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="editId">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 3fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Kode *</label>
                        <input type="text" name="kode" id="editKode" class="form-control" maxlength="10" style="text-transform:uppercase" required>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Nama Gejala *</label>
                        <input type="text" name="nama" id="editNama" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalEdit')">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Update</button>
            </div>
        </form>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editKode').value = data.kode;
    document.getElementById('editNama').value = data.nama;
    openModal('modalEdit');
}
</script>
</body>
</html>
