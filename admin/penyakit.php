<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyakit - SiPaGi Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<?php
/**
 * HALAMAN KELOLA PENYAKIT (CRUD)
 * Halaman admin untuk menambah, mengubah, dan menghapus data penyakit.
 * Penyakit inilah yang akan menjadi KONKLUSI dalam sistem forward chaining.
 * 
 * CRUD Operations:
 * - Create: Tambah penyakit baru
 * - Read: Tampilkan daftar penyakit
 * - Update: Edit penyakit yang ada
 * - Delete: Hapus penyakit
 */
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$flash = getFlash();

// =====================================================
// PROSES AKSI CRUD
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi     = $_POST['aksi'] ?? '';
    $kode     = strtoupper(clean($_POST['kode'] ?? ''));
    $nama     = clean($_POST['nama'] ?? '');
    $deskripsi = clean($_POST['deskripsi'] ?? '');
    $solusi   = clean($_POST['solusi'] ?? '');

    if ($aksi === 'tambah') {
        // Cek apakah kode sudah ada
        $cek = $conn->prepare("SELECT id FROM penyakit WHERE kode = ?");
        $cek->bind_param('s', $kode);
        $cek->execute();
        if ($cek->get_result()->num_rows > 0) {
            setFlash('danger', "Kode penyakit '$kode' sudah digunakan!");
        } elseif (empty($nama)) {
            setFlash('danger', 'Nama penyakit tidak boleh kosong!');
        } else {
            $stmt = $conn->prepare("INSERT INTO penyakit (kode, nama, deskripsi, solusi) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('ssss', $kode, $nama, $deskripsi, $solusi);
            if ($stmt->execute()) {
                setFlash('success', "Penyakit '$nama' berhasil ditambahkan!");
            } else {
                setFlash('danger', 'Gagal menambahkan data. Coba lagi!');
            }
        }

    } elseif ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("UPDATE penyakit SET kode=?, nama=?, deskripsi=?, solusi=? WHERE id=?");
        $stmt->bind_param('ssssi', $kode, $nama, $deskripsi, $solusi, $id);
        if ($stmt->execute()) {
            setFlash('success', "Penyakit berhasil diperbarui!");
        } else {
            setFlash('danger', 'Gagal memperbarui data!');
        }

    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM penyakit WHERE id = ?");
        $stmt->bind_param('i', $id);
        if ($stmt->execute()) {
            setFlash('success', 'Penyakit berhasil dihapus!');
        } else {
            setFlash('danger', 'Gagal menghapus data!');
        }
    }

    redirect(BASE_URL . '/admin/penyakit.php');
}

// Ambil data edit jika ada parameter ?edit=id
$editData = null;
if (isset($_GET['edit'])) {
    $editId = (int)$_GET['edit']; // Simpan ke variabel dulu sebelum di-pass ke bind_param
    $stmt = $conn->prepare("SELECT * FROM penyakit WHERE id = ?");
    $stmt->bind_param('i', $editId); // ✅ Sekarang pass variabel, bukan ekspresi langsung
    $stmt->execute();
    $editData = $stmt->get_result()->fetch_assoc();
}

// Ambil semua data penyakit beserta jumlah gejala dan aturan
$penyakitList = $conn->query("
    SELECT p.*, COUNT(a.id) as jumlah_aturan 
    FROM penyakit p 
    LEFT JOIN aturan a ON p.id = a.penyakit_id 
    GROUP BY p.id 
    ORDER BY p.kode
");
?>

<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button id="sidebarToggle" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:20px">☰</button>
                <div class="topbar-title">🦠 Kelola Data Penyakit</div>
            </div>
            <button onclick="openModal('modalTambah')" class="btn btn-primary btn-sm">
                + Tambah Penyakit
            </button>
        </div>

        <div class="page-content">
            <div class="breadcrumb">
                <a href="index.php">Dashboard</a>
                <span class="sep">›</span>
                <span>Data Penyakit</span>
            </div>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['type'] === 'success' ? '✅' : '❌' ?> <?= clean($flash['message']) ?>
            </div>
            <?php endif; ?>

            <!-- =====================================================
                 PANEL INFO
                 ===================================================== -->
            <div style="padding:16px 20px;background:rgba(14,165,233,0.06);border:1px solid rgba(14,165,233,0.15);border-radius:12px;margin-bottom:24px;font-size:13px;color:#7dd3fc">
                <strong>💡 Tentang Data Penyakit:</strong> Data penyakit merupakan <strong>konklusi (THEN)</strong> dalam aturan Forward Chaining. 
                Setiap penyakit memiliki sejumlah gejala yang dihubungkan melalui <strong>aturan (Rules)</strong>.
            </div>

            <!-- Search + Tabel -->
            <div class="card">
                <div class="card-header">
                    <div class="card-title">📋 Daftar Penyakit Gigi (<?= $penyakitList->num_rows ?> data)</div>
                    <div class="search-box" style="max-width:250px">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="tableSearch" placeholder="Cari penyakit...">
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Penyakit</th>
                                <th>Deskripsi</th>
                                <th>Jumlah Gejala</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($penyakitList->num_rows === 0): ?>
                            <tr>
                                <td colspan="5" style="text-align:center;padding:40px;color:#64748b">
                                    Belum ada data penyakit. Klik <strong>+ Tambah Penyakit</strong> untuk memulai.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($p = $penyakitList->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-primary"><?= clean($p['kode']) ?></span>
                                </td>
                                <td>
                                    <div style="font-weight:600"><?= clean($p['nama']) ?></div>
                                </td>
                                <td style="max-width:300px">
                                    <div style="font-size:12px;color:#64748b;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:280px">
                                        <?= clean(substr($p['deskripsi'], 0, 80)) ?>...
                                    </div>
                                </td>
                                <td>
                                    <span class="badge badge-<?= $p['jumlah_aturan'] > 0 ? 'success' : 'warning' ?>">
                                        <?= $p['jumlah_aturan'] ?> gejala
                                    </span>
                                </td>
                                <td style="text-align:center">
                                    <div style="display:flex;gap:6px;justify-content:center">
                                        <!-- Tombol Edit -->
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                                class="btn btn-outline btn-sm">✏️ Edit</button>
                                        <!-- Tombol Hapus -->
                                        <form method="POST" onsubmit="return confirmDelete(this)">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $p['id'] ?>">
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

<!-- =====================================================
     MODAL TAMBAH PENYAKIT
     ===================================================== -->
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">➕ Tambah Penyakit Baru</div>
            <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Kode Penyakit *</label>
                        <input type="text" name="kode" class="form-control" placeholder="P009" required maxlength="10"
                               style="text-transform:uppercase">
                        <small style="color:#64748b;font-size:11px">Contoh: P009</small>
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Nama Penyakit *</label>
                        <input type="text" name="nama" class="form-control" placeholder="Nama penyakit..." required>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Deskripsi Penyakit</label>
                    <textarea name="deskripsi" class="form-control" rows="3" placeholder="Jelaskan tentang penyakit ini..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Solusi / Penanganan</label>
                    <textarea name="solusi" class="form-control" rows="3" placeholder="Rekomendasi penanganan..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('modalTambah')">Batal</button>
                <button type="submit" class="btn btn-primary">💾 Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- =====================================================
     MODAL EDIT PENYAKIT
     ===================================================== -->
<div class="modal-overlay" id="modalEdit">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">✏️ Edit Penyakit</div>
            <button class="modal-close" onclick="closeModal('modalEdit')">×</button>
        </div>
        <form method="POST" action="">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="editId">
            <div class="modal-body">
                <div style="display:grid;grid-template-columns:1fr 2fr;gap:16px">
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Kode *</label>
                        <input type="text" name="kode" id="editKode" class="form-control" required maxlength="10" style="text-transform:uppercase">
                    </div>
                    <div class="form-group" style="margin-bottom:0">
                        <label class="form-label">Nama Penyakit *</label>
                        <input type="text" name="nama" id="editNama" class="form-control" required>
                    </div>
                </div>
                <div class="form-group" style="margin-top:16px">
                    <label class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" id="editDeskripsi" class="form-control" rows="3"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Solusi</label>
                    <textarea name="solusi" id="editSolusi" class="form-control" rows="3"></textarea>
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
// Fungsi untuk mengisi modal edit dengan data penyakit
function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editKode').value = data.kode;
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editDeskripsi').value = data.deskripsi || '';
    document.getElementById('editSolusi').value = data.solusi || '';
    openModal('modalEdit');
}
</script>
</body>
</html>
