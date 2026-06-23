<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Penyakit - SiPaGi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.1">
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
                <button class="btn border-0 p-0 text-white-50 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                    <span class="fs-4">☰</span>
                </button>
                <div class="topbar-title">🦠 Kelola Data Penyakit</div>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
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
            <div class="card border-translucent">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                    <h5 class="card-title mb-0">📋 Daftar Penyakit Gigi (<?= $penyakitList->num_rows ?> data)</h5>
                    <div class="search-box ms-auto" style="max-width:250px">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Cari penyakit...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Kode</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Nama Penyakit</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Deskripsi</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Jumlah Gejala</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border); text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: none;">
                            <?php if ($penyakitList->num_rows === 0): ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    Belum ada data penyakit. Klik <strong>+ Tambah Penyakit</strong> untuk memulai.
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php while ($p = $penyakitList->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="background: transparent;">
                                    <span class="badge bg-primary text-white"><?= clean($p['kode']) ?></span>
                                </td>
                                <td style="background: transparent;">
                                    <div class="fw-semibold text-white"><?= clean($p['nama']) ?></div>
                                </td>
                                <td style="background: transparent; max-width:300px">
                                    <div class="text-muted text-truncate" style="font-size:12px; max-width:280px">
                                        <?= clean(substr($p['deskripsi'], 0, 80)) ?>...
                                    </div>
                                </td>
                                <td style="background: transparent;">
                                    <span class="badge bg-<?= $p['jumlah_aturan'] > 0 ? 'success' : 'warning' ?> text-white">
                                        <?= $p['jumlah_aturan'] ?> gejala
                                    </span>
                                </td>
                                <td style="background: transparent; text-align:center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <!-- Tombol Edit -->
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($p)) ?>)" 
                                                class="btn btn-outline-info btn-sm">✏️ Edit</button>
                                        <!-- Tombol Hapus -->
                                        <form method="POST" onsubmit="return confirmDelete(this)" class="m-0">
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
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-md);">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fs-6 fw-bold" id="modalTambahLabel">➕ Tambah Penyakit Baru</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-white-50">Kode Penyakit *</label>
                            <input type="text" name="kode" class="form-control bg-dark border-secondary text-white" placeholder="P009" required maxlength="10" style="text-transform:uppercase">
                            <small class="text-muted" style="font-size:11px">Contoh: P009</small>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-white-50">Nama Penyakit *</label>
                            <input type="text" name="nama" class="form-control bg-dark border-secondary text-white" placeholder="Nama penyakit..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Deskripsi Penyakit</label>
                            <textarea name="deskripsi" class="form-control bg-dark border-secondary text-white" rows="3" placeholder="Jelaskan tentang penyakit ini..."></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Solusi / Penanganan</label>
                            <textarea name="solusi" class="form-control bg-dark border-secondary text-white" rows="3" placeholder="Rekomendasi penanganan..."></textarea>
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

<!-- =====================================================
     MODAL EDIT PENYAKIT
     ===================================================== -->
<div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-md);">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fs-6 fw-bold" id="modalEditLabel">✏️ Edit Penyakit</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label text-white-50">Kode *</label>
                            <input type="text" name="kode" id="editKode" class="form-control bg-dark border-secondary text-white" required maxlength="10" style="text-transform:uppercase">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label text-white-50">Nama Penyakit *</label>
                            <input type="text" name="nama" id="editNama" class="form-control bg-dark border-secondary text-white" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Deskripsi</label>
                            <textarea name="deskripsi" id="editDeskripsi" class="form-control bg-dark border-secondary text-white" rows="3"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Solusi</label>
                            <textarea name="solusi" id="editSolusi" class="form-control bg-dark border-secondary text-white" rows="3"></textarea>
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
// Fungsi untuk mengisi modal edit dengan data penyakit
function openEditModal(data) {
    document.getElementById('editId').value = data.id;
    document.getElementById('editKode').value = data.kode;
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editDeskripsi').value = data.deskripsi || '';
    document.getElementById('editSolusi').value = data.solusi || '';
    editModal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
