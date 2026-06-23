<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Gejala - SiPaGi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.1">
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
                <button class="btn border-0 p-0 text-white-50 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                    <span class="fs-4">☰</span>
                </button>
                <div class="topbar-title">📝 Kelola Data Gejala</div>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
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

            <div class="card border-translucent">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                    <h5 class="card-title mb-0">📋 Daftar Gejala (<?= $gejalaList->num_rows ?> data)</h5>
                    <div class="search-box ms-auto" style="max-width:250px">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Cari gejala...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Kode</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Nama Gejala</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Dipakai oleh</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border); text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: none;">
                            <?php if ($gejalaList->num_rows === 0): ?>
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    Belum ada data gejala.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($g = $gejalaList->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="background: transparent;"><span class="badge bg-success text-white"><?= clean($g['kode']) ?></span></td>
                                <td style="background: transparent; font-size:14px" class="text-white"><?= clean($g['nama']) ?></td>
                                <td style="background: transparent;">
                                    <span class="badge bg-<?= $g['dipakai_oleh'] > 0 ? 'primary' : 'warning' ?> text-white">
                                        <?= $g['dipakai_oleh'] ?> penyakit
                                    </span>
                                </td>
                                <td style="background: transparent; text-align:center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($g)) ?>)" 
                                                class="btn btn-outline-info btn-sm">✏️ Edit</button>
                                        <form method="POST" onsubmit="return confirmDelete(this)" class="m-0">
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
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-md);">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fs-6 fw-bold" id="modalTambahLabel">➕ Tambah Gejala Baru</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Kode *</label>
                            <input type="text" name="kode" class="form-control bg-dark border-secondary text-white" placeholder="G026" maxlength="10" style="text-transform:uppercase" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label text-white-50">Nama Gejala *</label>
                            <input type="text" name="nama" class="form-control bg-dark border-secondary text-white" placeholder="Deskripsi gejala yang dialami pasien..." required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-translucent">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">💾 Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-md);">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fs-6 fw-bold" id="modalEditLabel">✏️ Edit Gejala</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label text-white-50">Kode *</label>
                            <input type="text" name="kode" id="editKode" class="form-control bg-dark border-secondary text-white" maxlength="10" style="text-transform:uppercase" required>
                        </div>
                        <div class="col-md-9">
                            <label class="form-label text-white-50">Nama Gejala *</label>
                            <input type="text" name="nama" id="editNama" class="form-control bg-dark border-secondary text-white" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-translucent">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">💾 Update</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
const editModal = new bootstrap.Modal(document.getElementById('modalEdit'));
function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editKode').value = data.kode;
    document.getElementById('editNama').value = data.nama;
    editModal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
