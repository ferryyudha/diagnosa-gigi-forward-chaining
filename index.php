<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="SiPaGi - Sistem Pakar Diagnosis Penyakit Gigi menggunakan Forward Chaining. Praktik Mandiri Drg. Hj. Rini Sutarti">
  <title>SiPaGi — Sistem Pakar Penyakit Gigi</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* ─── How it works ─── */
    .how-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(200px,1fr)); gap:0; position:relative; margin-top:48px; }
    .how-grid::before {
      content:'';
      position:absolute;
      top:32px; left:calc(12.5%); right:calc(12.5%);
      height:1px;
      background: linear-gradient(90deg,transparent,rgba(14,165,233,0.4),rgba(14,165,233,0.4),transparent);
    }
    .how-step { text-align:center; padding:0 20px; }
    .how-num {
      width:64px; height:64px;
      background: linear-gradient(135deg,#0ea5e9,#06b6d4);
      border-radius:20px;
      display:flex; align-items:center; justify-content:center;
      font-family:'Plus Jakarta Sans',sans-serif;
      font-size:22px; font-weight:900; color:#fff;
      margin:0 auto 20px;
      box-shadow:0 8px 28px rgba(14,165,233,0.35);
      position:relative; z-index:1;
    }
    .how-title { font-family:'Plus Jakarta Sans',sans-serif; font-size:15px; font-weight:700; margin-bottom:8px; }
    .how-desc  { font-size:13px; color:#64748b; line-height:1.7; }

    /* ─── CTA Section ─── */
    .cta-section {
      margin:0 40px 80px;
      padding:60px 40px;
      background:linear-gradient(135deg,rgba(14,165,233,0.08),rgba(6,182,212,0.04));
      border:1px solid rgba(14,165,233,0.15);
      border-radius:28px;
      text-align:center;
      position:relative; overflow:hidden;
    }
    .cta-section::before {
      content:'';
      position:absolute;
      width:400px; height:400px;
      background:radial-gradient(circle,rgba(14,165,233,0.1) 0%,transparent 70%);
      top:-150px; right:-80px; border-radius:50%;
    }
    .cta-section > * { position:relative; z-index:1; }

    /* ─── Footer ─── */
    footer {
      background:#060b18;
      border-top:1px solid rgba(255,255,255,0.05);
      padding:40px;
      display:grid;
      grid-template-columns:1fr auto;
      gap:24px;
      align-items:center;
    }
    .footer-brand { display:flex; align-items:center; gap:10px; margin-bottom:8px; }
    .footer-brand-icon {
      width:32px; height:32px;
      background:linear-gradient(135deg,#0ea5e9,#06b6d4);
      border-radius:8px;
      display:flex; align-items:center; justify-content:center;
      font-size:14px;
    }
    .footer-brand-name { font-family:'Plus Jakarta Sans',sans-serif; font-weight:800; font-size:16px; }
    .footer-copy { font-size:12px; color:#475569; line-height:1.7; }
    .footer-links { display:flex; gap:20px; }
    .footer-link { font-size:13px; color:#64748b; transition:color .15s; }
    .footer-link:hover { color:#38bdf8; }

    @media(max-width:768px) {
      footer { grid-template-columns:1fr; }
      .footer-links { flex-wrap:wrap; gap:12px; }
      .how-grid::before { display:none; }
      .cta-section { margin:0 16px 60px; padding:40px 20px; }
    }
  </style>
</head>
<body>

<!-- ══════════════════════════════════════
     NAVBAR
══════════════════════════════════════ -->
<nav class="navbar-home" id="navbar">
  <a href="index.php" class="navbar-brand" style="color:inherit;text-decoration:none">
    <div class="navbar-brand-icon">🦷</div>
    <span class="navbar-brand-text">SiPaGi</span>
  </a>
  <div class="navbar-links">
    <a href="#fitur" class="navbar-link">Fitur</a>
    <a href="#cara-kerja" class="navbar-link">Cara Kerja</a>
  </div>
  <div style="display:flex;gap:8px;align-items:center">
    <a href="pages/konsultasi.php" class="btn btn-outline btn-sm">Konsultasi</a>
    <a href="auth/login.php" class="btn btn-primary btn-sm">Login Admin</a>
  </div>
</nav>

<!-- ══════════════════════════════════════
     HERO
══════════════════════════════════════ -->
<section class="hero">
  <div class="hero-mesh"></div>
  <div class="hero-grid"></div>

  <div class="hero-content">
    <div class="hero-eyebrow">
      🦷 &nbsp;Sistem Pakar Berbasis AI · Forward Chaining
    </div>

    <h1 class="hero-title">
      Diagnosa Penyakit Gigi<br>
      <span class="line2">Lebih Cepat & Akurat</span>
    </h1>

    <p class="hero-desc">
      Sistem pakar cerdas yang membantu mendiagnosa penyakit gigi berdasarkan gejala yang Anda rasakan.
      Dikembangkan untuk <strong style="color:#cbd5e1">Praktik Mandiri Drg. Hj. Rini Sutarti</strong>
      menggunakan metode inferensi <em>forward chaining</em>.
    </p>

    <div class="hero-cta">
      <a href="pages/konsultasi.php" class="cta-primary">
        <span>🔍</span> Mulai Konsultasi Gratis
      </a>
      <a href="#cara-kerja" class="cta-secondary">
        <span>📖</span> Cara Kerja
      </a>
    </div>

    <!-- Stats Bar -->
    <div class="hero-stats">
      <div class="hero-stat">
        <div class="hero-stat-value">8</div>
        <div class="hero-stat-label">Jenis Penyakit</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-value">25</div>
        <div class="hero-stat-label">Basis Gejala</div>
      </div>
      <div class="hero-stat">
        <div class="hero-stat-value">40</div>
        <div class="hero-stat-label">Aturan (Rules)</div>
      </div>
    </div>
  </div>
</section>

<!-- ══════════════════════════════════════
     FITUR
══════════════════════════════════════ -->
<div class="section" id="fitur">
  <div class="section-center">
    <div class="section-tag">✨ Fitur Utama</div>
    <h2 class="section-title">Mengapa Pilih SiPaGi?</h2>
    <p class="section-desc">Dirancang khusus untuk membantu pasien dan dokter dalam proses diagnosa awal penyakit gigi</p>
  </div>

  <div class="features-grid">
    <div class="feature-card">
      <div class="feature-icon">🤖</div>
      <div class="feature-title">Engine Forward Chaining</div>
      <p class="feature-desc">Algoritma forward chaining menelusuri fakta (gejala) menuju kesimpulan (penyakit) secara sistematis.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📊</div>
      <div class="feature-title">Persentase Kecocokan</div>
      <p class="feature-desc">Menghitung dan menampilkan tingkat kesesuaian antara gejala yang dirasakan dengan basis pengetahuan.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">📋</div>
      <div class="feature-title">Riwayat Konsultasi</div>
      <p class="feature-desc">Menyimpan seluruh histori konsultasi pasien untuk referensi dokter dan evaluasi kondisi pasien.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">⚙️</div>
      <div class="feature-title">Panel Admin Lengkap</div>
      <p class="feature-desc">Manajemen basis pengetahuan (penyakit, gejala, aturan) secara mudah melalui panel yang intuitif.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">💊</div>
      <div class="feature-title">Rekomendasi Penanganan</div>
      <p class="feature-desc">Memberikan informasi penyakit dan rekomendasi penanganan medis yang sesuai dengan hasil diagnosa.</p>
    </div>
    <div class="feature-card">
      <div class="feature-icon">🖨️</div>
      <div class="feature-title">Cetak Hasil Diagnosa</div>
      <p class="feature-desc">Hasil diagnosa dapat dicetak sebagai laporan yang dapat dibawa saat konsultasi langsung.</p>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     CARA KERJA
══════════════════════════════════════ -->
<div style="background:rgba(255,255,255,0.01);border-top:1px solid rgba(255,255,255,0.05);border-bottom:1px solid rgba(255,255,255,0.05)">
  <div class="section" id="cara-kerja">
    <div class="section-center">
      <div class="section-tag">🔬 Metode Ilmiah</div>
      <h2 class="section-title">Cara Kerja Forward Chaining</h2>
      <p class="section-desc">Sistem menelusuri dari fakta (gejala) menuju kesimpulan (diagnosa penyakit) secara otomatis</p>
    </div>

    <div class="how-grid">
      <div class="how-step">
        <div class="how-num">1</div>
        <div class="how-title">Input Gejala</div>
        <p class="how-desc">Pasien memilih gejala yang dirasakan dari daftar gejala yang tersedia di sistem</p>
      </div>
      <div class="how-step">
        <div class="how-num">2</div>
        <div class="how-title">Mesin Inferensi</div>
        <p class="how-desc">Sistem mencocokkan gejala (fakta) dengan aturan IF-THEN dalam basis pengetahuan</p>
      </div>
      <div class="how-step">
        <div class="how-num">3</div>
        <div class="how-title">Hitung Kecocokan</div>
        <p class="how-desc">Dihitung persentase kesesuaian gejala terhadap setiap kemungkinan penyakit</p>
      </div>
      <div class="how-step">
        <div class="how-num">4</div>
        <div class="how-title">Hasil Diagnosa</div>
        <p class="how-desc">Sistem menampilkan diagnosa beserta deskripsi penyakit dan rekomendasi penanganan</p>
      </div>
    </div>
  </div>
</div>

<!-- ══════════════════════════════════════
     CTA
══════════════════════════════════════ -->
<div class="section" style="padding-bottom:0">
  <div class="cta-section">
    <div style="font-size:48px;margin-bottom:16px">🦷</div>
    <h2 style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(22px,3vw,34px);font-weight:800;letter-spacing:-1px;margin-bottom:12px">
      Siap Memulai Konsultasi?
    </h2>
    <p style="color:#64748b;font-size:16px;max-width:400px;margin:0 auto 28px;line-height:1.7">
      Dapatkan diagnosa awal penyakit gigi secara gratis dan cepat. Tidak perlu registrasi!
    </p>
    <a href="pages/konsultasi.php" class="cta-primary" style="font-size:15px">
      🔍 Mulai Konsultasi Sekarang
    </a>
  </div>
</div>

<!-- ══════════════════════════════════════
     FOOTER
══════════════════════════════════════ -->
<footer>
  <div>
    <div class="footer-brand">
      <div class="footer-brand-icon">🦷</div>
      <span class="footer-brand-name">SiPaGi</span>
    </div>
    <p class="footer-copy">
      Sistem Pakar Penyakit Gigi · Metode Forward Chaining<br>
      © <?= date('Y') ?> Praktik Mandiri <strong style="color:#94a3b8">Drg. Hj. Rini Sutarti</strong>
    </p>
  </div>
  <div class="footer-links">
    <a href="pages/konsultasi.php" class="footer-link">Konsultasi</a>
    <a href="pages/riwayat.php" class="footer-link">Riwayat</a>
    <a href="auth/login.php" class="footer-link">Admin</a>
  </div>
</footer>

<script>
// Navbar scroll effect
const nav = document.getElementById('navbar');
window.addEventListener('scroll', () => {
  nav.classList.toggle('scrolled', window.scrollY > 40);
}, { passive: true });

// Smooth scroll
document.querySelectorAll('a[href^="#"]').forEach(a => {
  a.addEventListener('click', e => {
    const t = document.querySelector(a.getAttribute('href'));
    if (t) { e.preventDefault(); t.scrollIntoView({ behavior:'smooth', block:'start' }); }
  });
});

// Intersection observer for fade-in animations
const observer = new IntersectionObserver((entries) => {
  entries.forEach(e => {
    if (e.isIntersecting) {
      e.target.style.opacity = '1';
      e.target.style.transform = 'translateY(0)';
    }
  });
}, { threshold: 0.1 });

document.querySelectorAll('.feature-card, .how-step, .hero-stat').forEach(el => {
  el.style.opacity = '0';
  el.style.transform = 'translateY(20px)';
  el.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
  observer.observe(el);
});
</script>
</body>
</html>
