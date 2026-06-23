<?php
/**
 * PARTIAL: SIDEBAR ADMIN
 * Komponen sidebar yang digunakan di semua halaman admin.
 */
$currentPage = basename($_SERVER['PHP_SELF']);
$currentDir  = basename(dirname($_SERVER['PHP_SELF']));

function isActive($page) {
    global $currentPage;
    return $currentPage === $page ? 'active' : '';
}

// Menu definition
$menuUtama = [
    ['href' => BASE_URL . '/admin/index.php',    'icon' => '📊', 'label' => 'Dashboard',
     'active' => $currentPage === 'index.php' && $currentDir === 'admin'],
    ['href' => BASE_URL . '/pages/konsultasi.php','icon' => '🔍', 'label' => 'Konsultasi Pasien',
     'active' => $currentPage === 'konsultasi.php'],
    ['href' => BASE_URL . '/pages/riwayat.php',  'icon' => '📋', 'label' => 'Riwayat Konsultasi',
     'active' => $currentPage === 'riwayat.php'],
];
$menuKnowledge = [
    ['href' => BASE_URL . '/admin/penyakit.php', 'icon' => '🦠', 'label' => 'Data Penyakit',
     'active' => $currentPage === 'penyakit.php'],
    ['href' => BASE_URL . '/admin/gejala.php',   'icon' => '📝', 'label' => 'Data Gejala',
     'active' => $currentPage === 'gejala.php'],
    ['href' => BASE_URL . '/admin/aturan.php',   'icon' => '⚙️', 'label' => 'Aturan (Rules)',
     'active' => $currentPage === 'aturan.php'],
];
$menuSystem = [
    ['href' => BASE_URL . '/admin/pengguna.php', 'icon' => '👥', 'label' => 'Data Pengguna',
     'active' => $currentPage === 'pengguna.php'],
    ['href' => BASE_URL . '/index.php',          'icon' => '🏠', 'label' => 'Halaman Utama',
     'active' => false],
];
?>

<aside class="sidebar offcanvas-lg offcanvas-start border-end border-translucent" tabindex="-1" id="sidebar" style="background: var(--bg-surface);">
    <!-- Mobile Header -->
    <div class="offcanvas-header d-lg-none px-4 pt-4 pb-2 justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="brand-icon" style="font-size: 20px;">🦷</div>
            <span class="fw-bold text-white fs-5">SiPaGi</span>
        </div>
        <button type="button" class="btn-close text-reset bg-light" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Close"></button>
    </div>

    <div class="offcanvas-body d-flex flex-column h-100 p-0">
        <!-- Brand (visible on desktop) -->
        <div class="sidebar-brand d-none d-lg-block">
            <a href="<?= BASE_URL ?>/admin/index.php" style="text-decoration:none" class="brand-logo">
                <div class="brand-icon">🦷</div>
                <div>
                    <div class="brand-name">SiPaGi</div>
                </div>
            </a>
            <div class="brand-subtitle">Sistem Pakar Penyakit Gigi<br>Metode Forward Chaining</div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav flex-grow-1 px-3 py-3">
            <div class="nav-section-title">Menu Utama</div>
            <?php foreach ($menuUtama as $m): ?>
            <div class="nav-item">
                <a href="<?= $m['href'] ?>" class="nav-link <?= $m['active'] ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $m['icon'] ?></span>
                    <?= $m['label'] ?>
                </a>
            </div>
            <?php endforeach; ?>

            <div class="nav-section-title">Basis Pengetahuan</div>
            <?php foreach ($menuKnowledge as $m): ?>
            <div class="nav-item">
                <a href="<?= $m['href'] ?>" class="nav-link <?= $m['active'] ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $m['icon'] ?></span>
                    <?= $m['label'] ?>
                </a>
            </div>
            <?php endforeach; ?>

            <div class="nav-section-title">Sistem</div>
            <?php foreach ($menuSystem as $m): ?>
            <div class="nav-item">
                <a href="<?= $m['href'] ?>" class="nav-link <?= $m['active'] ? 'active' : '' ?>">
                    <span class="nav-icon"><?= $m['icon'] ?></span>
                    <?= $m['label'] ?>
                </a>
            </div>
            <?php endforeach; ?>
        </nav>

        <!-- User Footer -->
        <div class="sidebar-footer mt-auto border-top border-translucent">
            <div class="user-info">
                <div class="user-avatar">
                    <?= strtoupper(substr($_SESSION['nama'] ?? 'A', 0, 1)) ?>
                </div>
                <div style="flex:1;min-width:0">
                    <div class="user-name" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">
                        <?= htmlspecialchars($_SESSION['nama'] ?? 'Admin') ?>
                    </div>
                    <div class="user-role"><?= ucfirst($_SESSION['role'] ?? 'admin') ?></div>
                </div>
                <a href="<?= BASE_URL ?>/auth/logout.php" title="Keluar"
                   style="color:#475569;font-size:16px;padding:6px;border-radius:6px;transition:all 0.15s;display:flex;align-items:center"
                   onmouseover="this.style.background='rgba(239,68,68,0.1)';this.style.color='#fca5a5'"
                   onmouseout="this.style.background='';this.style.color='#475569'">
                   🚪
                </a>
            </div>
        </div>
    </div>
</aside>
