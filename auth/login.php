<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Admin — SiPaGi</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../assets/css/style.css?v=2.1">
  <style>
    .info-card {
      background: rgba(14,165,233,0.06);
      border: 1px solid rgba(14,165,233,0.15);
      border-radius: 10px;
      padding: 12px 16px;
      font-size: 12.5px;
      color: #7dd3fc;
      margin-top: 20px;
    }
    .info-card code {
      background: rgba(255,255,255,0.08);
      padding: 2px 7px;
      border-radius: 4px;
      font-family: monospace;
    }
    .divider-text {
      display: flex; align-items: center; gap: 12px;
      color: #475569; font-size: 12px; margin: 20px 0;
    }
    .divider-text::before, .divider-text::after {
      content: ''; flex: 1; height: 1px; background: rgba(255,255,255,0.07);
    }

    /* Animated left panel feature list */
    .feature-list { list-style: none; margin-top: 36px; text-align: left; }
    .feature-list li {
      display: flex; align-items: flex-start; gap: 12px;
      padding: 10px 0; border-bottom: 1px solid rgba(255,255,255,0.05);
      font-size: 13.5px; color: #94a3b8;
    }
    .feature-list li:last-child { border-bottom: none; }
    .feature-list .ficon {
      width: 28px; height: 28px; border-radius: 8px;
      background: rgba(14,165,233,0.1); border: 1px solid rgba(14,165,233,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; flex-shrink: 0; margin-top: 2px;
    }
    .feature-list .ftitle { font-weight: 600; color: #e2e8f0; font-size: 13px; }
    .feature-list .fdesc  { font-size: 12px; color: #64748b; margin-top: 2px; }
  </style>
</head>
<body>
<?php
require_once '../config/database.php';
require_once '../config/session.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['user_id'])) {
    redirect(BASE_URL . '/admin/index.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Username dan password harus diisi!';
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nama']     = $user['nama'];
            $_SESSION['role']     = $user['role'];
            setFlash('success', 'Selamat datang, ' . $user['nama'] . '!');
            redirect(BASE_URL . '/admin/index.php');
        } else {
            $error = 'Username atau password salah!';
        }
    }
}
?>

<div class="login-page">

  <!-- ══ LEFT PANEL ══ -->
  <div class="login-left">
    <div class="login-left-content">
      <div class="login-brand-icon">🦷</div>
      <div class="login-brand-name">SiPaGi</div>
      <p class="login-brand-desc">
        Sistem Pakar Penyakit Gigi berbasis metode<br>
        <strong style="color:#38bdf8">Forward Chaining</strong> —
        Praktik Mandiri Drg. Hj. Rini Sutarti
      </p>

      <ul class="feature-list">
        <li>
          <div class="ficon">🤖</div>
          <div>
            <div class="ftitle">Engine Forward Chaining</div>
            <div class="fdesc">Inferensi otomatis dari gejala ke diagnosa</div>
          </div>
        </li>
        <li>
          <div class="ficon">⚙️</div>
          <div>
            <div class="ftitle">Kelola Basis Pengetahuan</div>
            <div class="fdesc">Tambah penyakit, gejala, dan aturan IF-THEN</div>
          </div>
        </li>
        <li>
          <div class="ficon">📋</div>
          <div>
            <div class="ftitle">Riwayat Konsultasi</div>
            <div class="fdesc">Monitor histori diagnosa pasien</div>
          </div>
        </li>
        <li>
          <div class="ficon">📊</div>
          <div>
            <div class="ftitle">Dashboard & Statistik</div>
            <div class="fdesc">Visualisasi data diagnosa dengan grafik</div>
          </div>
        </li>
      </ul>

      <div class="login-dots">
        <div class="login-dot"></div>
        <div class="login-dot"></div>
        <div class="login-dot"></div>
      </div>
    </div>
  </div>

  <!-- ══ RIGHT PANEL ══ -->
  <div class="login-right">
    <div class="login-form-wrap">

      <!-- Header -->
      <div style="margin-bottom:32px">
        <div style="font-size:13px;color:#64748b;margin-bottom:6px">Panel Admin</div>
        <h1 class="login-welcome">Selamat Datang 👋</h1>
        <p class="login-sub">Masuk ke panel admin SiPaGi untuk mengelola sistem</p>
      </div>

      <!-- Error -->
      <?php if ($error): ?>
      <div class="alert alert-danger">
        <span>⚠️</span> <?= clean($error) ?>
      </div>
      <?php endif; ?>

      <!-- Form -->
      <form method="POST" id="loginForm">
        <div class="form-group">
          <label class="form-label" for="username">Username</label>
          <div class="input-group">
            <span class="input-icon">👤</span>
            <input type="text" name="username" id="username" class="form-control"
                   placeholder="Masukkan username..."
                   value="<?= clean($_POST['username'] ?? '') ?>"
                   autocomplete="username" autofocus required>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label" for="password">Password</label>
          <div class="input-group">
            <span class="input-icon">🔒</span>
            <input type="password" name="password" id="password" class="form-control"
                   placeholder="Masukkan password..."
                   autocomplete="current-password"
                   style="padding-right:44px"
                   required>
            <button type="button" id="togglePass"
              onclick="togglePassword()"
              style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;color:#64748b;cursor:pointer;font-size:16px;padding:4px">
              👁
            </button>
          </div>
        </div>

        <button type="submit" id="btnLogin" class="btn btn-primary w-100 btn-lg" style="margin-top:4px">
          <span id="btnText">Masuk ke Dashboard</span>
          <span id="btnLoader" style="display:none"><span class="loader"></span></span>
        </button>
      </form>

      <div class="divider-text">atau</div>

      <a href="../pages/konsultasi.php" class="btn btn-outline w-100" style="justify-content:center">
        🔍 Konsultasi sebagai Pasien
      </a>

      <!-- Default credentials hint -->
      <div class="info-card">
        <strong>🔑 Akun Default:</strong><br>
        Username: <code>admin</code> &nbsp;|&nbsp; Password: <code>password</code>
      </div>

    </div>
  </div>

</div>

<script>
function togglePassword() {
  const inp = document.getElementById('password');
  const btn = document.getElementById('togglePass');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  btn.textContent = inp.type === 'password' ? '👁' : '🙈';
}

document.getElementById('loginForm').addEventListener('submit', function() {
  document.getElementById('btnText').style.display = 'none';
  document.getElementById('btnLoader').style.display = 'inline-flex';
  document.getElementById('btnLogin').disabled = true;
});
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
