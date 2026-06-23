<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Aturan (Rules) - SiPaGi Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/style.css?v=2.1">
    <script src="https://unpkg.com/@phosphor-icons/web@2.1.1/src/index.js" defer></script>
</head>
<body>
<?php
/**
 * HALAMAN KELOLA ATURAN (BASIS PENGETAHUAN)
 * ============================================================
 * Aturan adalah JANTUNG dari sistem forward chaining!
 * Setiap aturan berbentuk: IF [Gejala] THEN [Penyakit]
 * 
 * Contoh:
 * IF (G001) Nyeri spontan DAN (G015) Nyeri berdenyut THEN Pulpitis
 * 
 * Di halaman ini admin dapat:
 * - Menambah relasi gejala-penyakit (aturan baru)
 * - Menghapus relasi yang tidak diperlukan
 * - Melihat semua aturan yang ada
 * ============================================================
 */
require_once '../config/database.php';
require_once '../config/session.php';
requireAdmin();

$flash = getFlash();

// =====================================================
// PROSES TAMBAH / HAPUS ATURAN
// =====================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'tambah') {
        $penyakit_id = (int)($_POST['penyakit_id'] ?? 0);
        $gejala_ids  = $_POST['gejala_id'] ?? []; // Array gejala yang dipilih

        if ($penyakit_id === 0 || empty($gejala_ids)) {
            setFlash('danger', 'Pilih penyakit dan minimal 1 gejala!');
        } else {
            $sukses = 0;
            $duplikat = 0;
            foreach ($gejala_ids as $gejala_id) {
                $gid = (int)$gejala_id;
                // Cek apakah aturan sudah ada
                $cek = $conn->prepare("SELECT id FROM aturan WHERE penyakit_id=? AND gejala_id=?");
                $cek->bind_param('ii', $penyakit_id, $gid);
                $cek->execute();
                if ($cek->get_result()->num_rows > 0) {
                    $duplikat++;
                } else {
                    $stmt = $conn->prepare("INSERT INTO aturan (penyakit_id, gejala_id) VALUES (?, ?)");
                    $stmt->bind_param('ii', $penyakit_id, $gid);
                    if ($stmt->execute()) $sukses++;
                }
            }
            $msg = "$sukses aturan berhasil ditambahkan!";
            if ($duplikat > 0) $msg .= " ($duplikat sudah ada, dilewati)";
            setFlash('success', $msg);
        }

    } elseif ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM aturan WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute() 
            ? setFlash('success', 'Aturan berhasil dihapus!') 
            : setFlash('danger', 'Gagal menghapus aturan!');
    } elseif ($aksi === 'hapus_semua_penyakit') {
        // Hapus semua aturan untuk satu penyakit tertentu
        $penyakit_id = (int)($_POST['penyakit_id'] ?? 0);
        $stmt = $conn->prepare("DELETE FROM aturan WHERE penyakit_id=?");
        $stmt->bind_param('i', $penyakit_id);
        $stmt->execute()
            ? setFlash('success', 'Semua aturan untuk penyakit tersebut berhasil dihapus!')
            : setFlash('danger', 'Gagal menghapus!');
    }

    redirect(BASE_URL . '/admin/aturan.php');
}

// Ambil daftar penyakit untuk dropdown
$penyakitList = $conn->query("SELECT * FROM penyakit ORDER BY kode");

// Ambil daftar gejala untuk checkbox
$gejalaList = $conn->query("SELECT * FROM gejala ORDER BY kode");

// Ambil semua aturan, dikelompokkan per penyakit
$aturanQuery = "
    SELECT a.id as aturan_id, p.id as p_id, p.kode as p_kode, p.nama as p_nama,
           g.id as g_id, g.kode as g_kode, g.nama as g_nama
    FROM aturan a
    JOIN penyakit p ON a.penyakit_id = p.id
    JOIN gejala g ON a.gejala_id = g.id
    ORDER BY p.kode, g.kode
";
$aturanResult = $conn->query($aturanQuery);

// Kelompokkan aturan per penyakit
$aturanGrouped = [];
while ($row = $aturanResult->fetch_assoc()) {
    $aturanGrouped[$row['p_id']]['penyakit'] = ['id' => $row['p_id'], 'kode' => $row['p_kode'], 'nama' => $row['p_nama']];
    $aturanGrouped[$row['p_id']]['gejala'][] = ['id' => $row['g_id'], 'kode' => $row['g_kode'], 'nama' => $row['g_nama'], 'aturan_id' => $row['aturan_id']];
}
?>

<div class="app-wrapper">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="topbar">
            <div style="display:flex;align-items:center;gap:12px">
                <button class="btn border-0 p-0 text-white-50 d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar">
                    <span class="fs-4">☰</span>
                </button>
                <div class="topbar-title">⚙️ Kelola Aturan (Rules) - Basis Pengetahuan</div>
            </div>
            <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                + Tambah Aturan
            </button>
        </div>

        <div class="page-content">
            <div class="breadcrumb">
                <a href="index.php">Dashboard</a>
                <span class="sep">›</span>
                <span>Aturan (Rules)</span>
            </div>

            <?php if ($flash): ?>
            <div class="alert alert-<?= $flash['type'] ?>">
                <?= $flash['type'] === 'success' ? '✅' : '❌' ?> <?= clean($flash['message']) ?>
            </div>
            <?php endif; ?>

            <!-- Penjelasan Forward Chaining -->
            <div style="padding:20px;background:rgba(168,85,247,0.06);border:1px solid rgba(168,85,247,0.15);border-radius:12px;margin-bottom:24px;font-size:13px;color:#c4b5fd">
                <strong>🧠 Basis Pengetahuan Forward Chaining:</strong><br>
                Aturan berbentuk <code style="background:rgba(255,255,255,0.1);padding:2px 6px;border-radius:4px">IF [Gejala1] AND [Gejala2] AND ... THEN [Penyakit]</code><br>
                Semakin banyak gejala yang cocok, semakin tinggi persentase diagnosa. Sistem akan menampilkan semua penyakit yang mungkin berdasarkan persentase tertinggi.
            </div>

            <!-- Aturan Per Penyakit -->
            <?php if (empty($aturanGrouped)): ?>
            <div class="card">
                <div class="card-body">
                    <div class="empty-state">
                        <div class="icon">⚙️</div>
                        <h3>Belum Ada Aturan</h3>
                        <p>Klik <strong>+ Tambah Aturan</strong> untuk menambahkan relasi gejala-penyakit</p>
                    </div>
                </div>
            </div>
            <?php else: ?>
            <div style="display:flex;flex-direction:column;gap:16px">
                <?php foreach ($aturanGrouped as $group): ?>
                <div class="card">
                    <div class="card-header">
                        <div style="display:flex;align-items:center;gap:10px">
                            <span class="badge badge-primary" style="font-size:13px"><?= clean($group['penyakit']['kode']) ?></span>
                            <div class="card-title"><?= clean($group['penyakit']['nama']) ?></div>
                            <span class="badge badge-info"><?= count($group['gejala']) ?> gejala</span>
                        </div>
                        <form method="POST" onsubmit="return confirm('Hapus semua aturan penyakit ini?')">
                            <input type="hidden" name="aksi" value="hapus_semua_penyakit">
                            <input type="hidden" name="penyakit_id" value="<?= $group['penyakit']['id'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">🗑 Hapus Semua</button>
                        </form>
                    </div>
                    <div class="card-body">
                        <!-- Visualisasi aturan IF-THEN -->
                        <div style="font-size:12px;color:#64748b;margin-bottom:12px">
                            <code style="color:#c4b5fd">IF</code> 
                            <?php foreach ($group['gejala'] as $idx => $g): ?>
                                <code style="color:#6ee7b7;background:rgba(16,185,129,0.1);padding:2px 8px;border-radius:4px"><?= clean($g['kode']) ?></code>
                                <?= $idx < count($group['gejala']) - 1 ? '<code style="color:#64748b"> AND </code>' : '' ?>
                            <?php endforeach; ?>
                            <code style="color:#c4b5fd"> THEN </code>
                            <code style="color:#38bdf8;background:rgba(14,165,233,0.1);padding:2px 8px;border-radius:4px"><?= clean($group['penyakit']['kode']) ?></code>
                        </div>
                        <!-- Gejala tags dengan tombol hapus -->
                        <div class="gejala-tags">
                            <?php foreach ($group['gejala'] as $g): ?>
                            <div style="display:flex;align-items:center;gap:4px;background:rgba(16,185,129,0.08);border:1px solid rgba(16,185,129,0.2);border-radius:20px;padding:4px 12px">
                                <span style="font-size:11px;font-weight:600;color:#10b981"><?= clean($g['kode']) ?></span>
                                <span style="font-size:12px;color:#94a3b8"><?= clean($g['nama']) ?></span>
                                <form method="POST" style="margin:0" onsubmit="return confirm('Hapus aturan ini?')">
                                    <input type="hidden" name="aksi" value="hapus">
                                    <input type="hidden" name="id" value="<?= $g['aturan_id'] ?>">
                                    <button type="submit" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:14px;padding:0 2px;line-height:1">×</button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>

<!-- Modal Tambah Aturan -->
<div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-md);">
            <div class="modal-header border-bottom border-translucent">
                <h5 class="modal-title fs-6 fw-bold" id="modalTambahLabel">⚙️ Tambah Aturan Baru</h5>
                <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST">
                <input type="hidden" name="aksi" value="tambah">
                <div class="modal-body">
                    <!-- Pilih Penyakit (THEN) -->
                    <div class="mb-3">
                        <label class="form-label text-white-50">🦠 Penyakit (THEN) *</label>
                        <select name="penyakit_id" class="form-select bg-dark border-secondary text-white" required>
                            <option value="">-- Pilih Penyakit --</option>
                            <?php 
                            $penyakitList->data_seek(0);
                            while ($p = $penyakitList->fetch_assoc()): ?>
                            <option value="<?= $p['id'] ?>">[<?= $p['kode'] ?>] <?= clean($p['nama']) ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>

                    <!-- Pilih Gejala (IF) - Multiple Checkbox -->
                    <div class="mb-3">
                        <label class="form-label text-white-50">📝 Gejala (IF) * — Pilih satu atau lebih</label>
                        <div style="background:rgba(255,255,255,0.03);border:1px solid rgba(255,255,255,0.08);border-radius:10px;max-height:320px;overflow-y:auto;padding:12px">
                            <?php 
                            $gejalaList->data_seek(0);
                            while ($g = $gejalaList->fetch_assoc()): ?>
                            <label style="display:flex;align-items:center;gap:10px;padding:8px 10px;border-radius:8px;cursor:pointer;transition:background 0.15s">
                                <input type="checkbox" name="gejala_id[]" value="<?= $g['id'] ?>" 
                                       style="width:16px;height:16px;accent-color:#0ea5e9;cursor:pointer">
                                <span class="badge bg-success text-white" style="font-size:10px;flex-shrink:0"><?= $g['kode'] ?></span>
                                <span style="font-size:13px;color:#cbd5e1"><?= clean($g['nama']) ?></span>
                            </label>
                            <?php endwhile; ?>
                        </div>
                        <div style="margin-top:8px;display:flex;gap:10px">
                            <button type="button" onclick="checkAll(true)" style="background:none;border:none;color:#38bdf8;cursor:pointer;font-size:12px">✅ Pilih Semua</button>
                            <button type="button" onclick="checkAll(false)" style="background:none;border:none;color:#ef4444;cursor:pointer;font-size:12px">❌ Batal Semua</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top border-translucent">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">💾 Simpan Aturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="../assets/js/main.js"></script>
<script>
function checkAll(state) {
    document.querySelectorAll('#modalTambah input[type=checkbox]').forEach(cb => cb.checked = state);
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
