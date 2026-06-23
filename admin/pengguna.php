<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengguna - SiPaGi Admin</title>
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
                <button id="sidebarToggle" style="background:none;border:none;color:#94a3b8;cursor:pointer;font-size:20px">☰</button>
                <div class="topbar-title">👤 Kelola Pengguna</div>
            </div>
            <button onclick="openModal('modalTambah')" class="btn btn-primary btn-sm">
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

            <div class="card">
                <div class="card-header">
                    <div class="card-title">👥 Daftar Pengguna</div>
                    <div class="search-box" style="max-width:250px">
                        <span class="search-icon">🔍</span>
                        <input type="text" id="tableSearch" placeholder="Cari pengguna...">
                    </div>
                </div>
                <div class="table-wrapper">
                    <table>
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Terdaftar</th>
                                <th style="text-align:center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no = 1; while ($u = $userList->fetch_assoc()): ?>
                            <tr>
                                <td><?= $no++ ?></td>
                                <td>
                                    <div style="display:flex;align-items:center;gap:10px">
                                        <div style="width:32px;height:32px;background:linear-gradient(135deg,#0ea5e9,#06b6d4);border-radius:50%;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:13px">
                                            <?= strtoupper(substr($u['nama'], 0, 1)) ?>
                                        </div>
                                        <div style="font-weight:500"><?= clean($u['nama']) ?></div>
                                    </div>
                                </td>
                                <td style="color:#94a3b8">@<?= clean($u['username']) ?></td>
                                <td>
                                    <span class="badge badge-<?= $u['role'] === 'admin' ? 'danger' : 'info' ?>">
                                        <?= $u['role'] === 'admin' ? '👑 Admin' : '👤 User' ?>
                                    </span>
                                </td>
                                <td style="color:#64748b;font-size:12px"><?= date('d M Y', strtotime($u['created_at'])) ?></td>
                                <td style="text-align:center">
                                    <div style="display:flex;gap:6px;justify-content:center">
                                        <button onclick="openEditModal(<?= htmlspecialchars(json_encode($u)) ?>)" 
                                                class="btn btn-outline btn-sm">✏️ Edit</button>
                                        <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                        <form method="POST" onsubmit="return confirmDelete(this)">
                                            <input type="hidden" name="aksi" value="hapus">
                                            <input type="hidden" name="id" value="<?= $u['id'] ?>">
                                            <button type="submit" class="btn btn-danger btn-sm">🗑</button>
                                        </form>
                                        <?php else: ?>
                                        <span class="badge badge-warning" style="font-size:10px">Anda</span>
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
<div class="modal-overlay" id="modalTambah">
    <div class="modal">
        <div class="modal-header">
            <div class="modal-title">➕ Tambah Pengguna</div>
            <button class="modal-close" onclick="closeModal('modalTambah')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="tambah">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" class="form-control" placeholder="Nama lengkap..." required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" placeholder="username (tanpa spasi)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" placeholder="Min. 6 karakter" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" class="form-control">
                        <option value="user">👤 User (Pasien)</option>
                        <option value="admin">👑 Admin</option>
                    </select>
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
            <div class="modal-title">✏️ Edit Pengguna</div>
            <button class="modal-close" onclick="closeModal('modalEdit')">×</button>
        </div>
        <form method="POST">
            <input type="hidden" name="aksi" value="edit">
            <input type="hidden" name="id" id="editId">
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap *</label>
                    <input type="text" name="nama" id="editNama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" id="editUsername" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru <small style="color:#64748b">(kosongkan jika tidak diubah)</small></label>
                    <input type="password" name="password" class="form-control" placeholder="Isi untuk ubah password...">
                </div>
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role" id="editRole" class="form-control">
                        <option value="user">👤 User</option>
                        <option value="admin">👑 Admin</option>
                    </select>
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
    document.getElementById('editNama').value = data.nama;
    document.getElementById('editUsername').value = data.username;
    document.getElementById('editRole').value = data.role;
    openModal('modalEdit');
}
</script>
</body>
</html>
