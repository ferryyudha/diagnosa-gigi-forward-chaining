<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - SiPaGi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<?php
/**
 * HALAMAN KELOLA PENGGUNA
 * Admin dapat menambah, mengedit, dan menghapus akun pengguna sistem.
 * Password disimpan dengan bcrypt hashing untuk keamanan.
 */
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi     = $_POST['aksi'] ?? '';
    $nama     = clean($_POST['nama'] ?? '');
    $username = clean($_POST['username'] ?? '');
    $role     = in_array($_POST['role'] ?? '', ['admin','user']) ? $_POST['role'] : 'user';

    if ($aksi === 'tambah') {
        $password = $_POST['password'] ?? '';
        if (empty($nama) || empty($username) || empty($password)) {
            setFlash('danger', 'Semua field harus diisi!');
        } else {
            // Hash password dengan bcrypt
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO users (nama, username, password, role) VALUES (?,?,?,?)");
            $stmt->bind_param('ssss', $nama, $username, $hash, $role);
            $stmt->execute() 
                ? setFlash('success', "Pengguna '$nama' berhasil ditambahkan!") 
                : setFlash('danger', 'Gagal! Username mungkin sudah digunakan.');
        }
    } elseif ($aksi === 'edit') {
        $id = (int)($_POST['id'] ?? 0);
        $password = $_POST['password'] ?? '';
        
        if (!empty($password)) {
            // Jika password diisi, update password juga
            $hash = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, password=?, role=? WHERE id=?");
            $stmt->bind_param('ssssi', $nama, $username, $hash, $role, $id);
        } else {
            // Jika password kosong, biarkan password lama
            $stmt = $conn->prepare("UPDATE users SET nama=?, username=?, role=? WHERE id=?");
            $stmt->bind_param('sssi', $nama, $username, $role, $id);
        }
        $stmt->execute() 
            ? setFlash('success', 'Pengguna berhasil diperbarui!') 
            : setFlash('danger', 'Gagal memperbarui!');
    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        // Cegah menghapus diri sendiri
        if ($id === (int)$_SESSION['user_id']) {
            setFlash('danger', 'Tidak bisa menghapus akun Anda sendiri!');
        } else {
            $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
            $stmt->bind_param('i', $id);
            $stmt->execute() 
                ? setFlash('success', 'Pengguna berhasil dihapus!') 
                : setFlash('danger', 'Gagal menghapus!');
        }
    }
    redirect(BASE_URL . '/admin/pengguna.php');
}

$userList = $conn->query("SELECT * FROM users ORDER BY role, nama");
?>

<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button class="btn border-0 p-0 text-white-50 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                    <span class="fs-4">☰</span>
                </button>
                <div class="topbar-title">👤 Kelola Pengguna</div>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                + Tambah Pengguna
            </button>
        </div>

        <div class="page-content">
            <div class="breadcrumb">
                <a href="index.php">Dashboard</a>
                <span class="sep">›</span>
                <span>Pengguna</span>
            </div>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['type'] === 'success' ? '✅' : '❌' ?> <?= clean($flash['message']) ?>
            </div>
            <?php endif; ?>

            <div class="card border-translucent">
                <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2 py-3">
                    <h5 class="card-title mb-0">👥 Daftar Pengguna</h5>
                    <div class="search-box ms-auto" style="max-width:250px">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="tableSearch" class="form-control form-control-sm" placeholder="Cari pengguna...">
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">#</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Nama</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Username</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Role</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border);">Terdaftar</th>
                                <th style="background: var(--bg-surface); color: var(--text-200); border-bottom: 1px solid var(--border); text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody style="border-top: none;">
                            <?php $no = 1; while ($u = $userList->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid var(--border);">
                                <td style="background: transparent;"><?= $no++ ?></td>
                                <td style="background: transparent;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px;color: #fff;">
                                            <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                        </div>
                                        <div class="fw-semibold text-white"><?= clean($u['nama']) ?></div>
                                    </div>
                                </td>
                                <td style="background: transparent;" class="text-muted">@<?= clean($u['username']) ?></td>
                                <td style="background: transparent;">
                                    <span class="badge bg-<?= $u['role'] === 'admin' ? 'danger' : 'info' ?> text-white">
                                        <?= $u['role'] === 'admin' ? '👑 Admin' : '👤 User' ?>
                                    </span>
                                </td>
                                <td style="background: transparent;" class="text-muted" style="font-size:12px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td style="background: transparent; text-align:center">
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" 
                                                class="btn btn-outline-info btn-sm">✏️ Edit</button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" onsubmit="return confirmDelete(this)" class="m-0">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus</button>
                                        </form>
                                        <?php else: ?>
                                        <span class="badge bg-warning text-white" style="font-size:10px">Anda</span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
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
                <h5 class="modal-title fs-6 fw-bold" id="modalTambahLabel">➕ Tambah Pengguna</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-white-50">Nama Lengkap *</label>
                            <input type="text" name="nama" class="form-control bg-dark border-secondary text-white" placeholder="Nama lengkap..." required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Username *</label>
                            <input type="text" name="username" class="form-control bg-dark border-secondary text-white" placeholder="username (tanpa spasi)" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Password *</label>
                            <input type="password" name="password" class="form-control bg-dark border-secondary text-white" placeholder="Min. 6 karakter" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Role</label>
                            <select name="role" class="form-select bg-dark border-secondary text-white">
                                <option value="user">👤 User (Pasien)</option>
                                <option value="admin">👑 Admin</option>
                            </select>
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
                <h5 class="modal-title fs-6 fw-bold" id="modalEditLabel">✏️ Edit Pengguna</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="editId">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-white-50">Nama Lengkap *</label>
                            <input type="text" name="nama" id="editNama" class="form-control bg-dark border-secondary text-white" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Username *</label>
                            <input type="text" name="username" id="editUsername" class="form-control bg-dark border-secondary text-white" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                            <input type="password" name="password" class="form-control bg-dark border-secondary text-white" placeholder="Isi untuk ubah password...">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-white-50">Role</label>
                            <select name="role" id="editRole" class="form-select bg-dark border-secondary text-white">
                                <option value="user">👤 User</option>
                                <option value="admin">👑 Admin</option>
                            </select>
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
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editUsername').value = data.username;
    document.getElementById('editRole').value = data.role;
    editModal.show();
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
