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

<aside class="sidebar" id="sidebar">

    <!-- Brand -->
    <div class="sidebar-brand">
        <a href="<?= BASE_URL ?>/admin/index.php" style="text-decoration:none" class="brand-logo">
            <div class="brand-icon">🦷</div>
            <div>
                <div class="brand-name">SiPaGi</div>
            </div>
        </a>
        <div class="brand-subtitle">Sistem Pakar Penyakit Gigi<br>Metode Forward Chaining</div>
    </div>

    <!-- Navigation -->
    <nav class="sidebar-nav">

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
    <div class="sidebar-footer">
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

</aside>

<!-- Mobile Backdrop -->
<div id="sidebarOverlay" onclick="closeSidebar()"
     style="position:fixed;inset:0;background:rgba(0,0,0,0.7);z-index:150;opacity:0;visibility:hidden;transition:all 0.25s;backdrop-filter:blur(4px)">
</div>

<script>
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').style.opacity = '0';
    document.getElementById('sidebarOverlay').style.visibility = 'hidden';
}
// Toggle
const toggleBtn = document.getElementById('sidebarToggle');
if (toggleBtn) {
    toggleBtn.addEventListener('click', function() {
        const isOpen = document.getElementById('sidebar').classList.toggle('open');
        document.getElementById('sidebarOverlay').style.opacity = isOpen ? '1' : '0';
        document.getElementById('sidebarOverlay').style.visibility = isOpen ? 'visible' : 'hidden';
    });
}
</script>
