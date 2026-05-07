<?php
session_start();
require_once 'config/koneksi.php';

// Redirect kalau sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); exit;
}

// Deteksi BASE URL
$script_url = str_replace('\\', '/', $_SERVER['PHP_SELF']);
$parts = explode('/', trim($script_url, '/'));
$base_index = array_search('STOKOPNAMETEXTILE', $parts);
$BASE = ($base_index !== false) ? '/' . implode('/', array_slice($parts, 0, $base_index + 1)) . '/' : '/STOKOPNAMETEXTILE/';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND status = 'aktif' LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id']      = $user['id'];
            $_SESSION['username']     = $user['username'];
            $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
            $_SESSION['role']         = $user['role'];
            $_SESSION['foto']         = $user['foto'];
            header('Location: index.php'); exit;
        } else {
            // Coba cek plain text (untuk development)
            $cek = $conn->prepare("SELECT * FROM admin WHERE username = ? AND status = 'aktif' LIMIT 1");
            $cek->bind_param("s", $username);
            $cek->execute();
            $res = $cek->get_result();
            $u2  = $res->fetch_assoc();
            if ($u2 && $u2['password'] === $password) {
                // Password plain (update ke hash)
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $conn->query("UPDATE admin SET password='$hash' WHERE id={$u2['id']}");
                $_SESSION['user_id']      = $u2['id'];
                $_SESSION['username']     = $u2['username'];
                $_SESSION['nama_lengkap'] = $u2['nama_lengkap'];
                $_SESSION['role']         = $u2['role'];
                $_SESSION['foto']         = $u2['foto'];
                header('Location: index.php'); exit;
            }
            $error = 'Username atau password salah!';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Stock Opname Textile</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=Playfair+Display:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $BASE ?>assets/style.css">
</head>
<body>
<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
</div>

<div class="login-page">
    <!-- LEFT PANEL -->
    <div class="login-left">
        <div class="login-bg-art"></div>
        <div class="login-textile-pattern"></div>
        <div class="login-brand">
            <div class="brand-icon">🧵</div>
            <h1>Stock Opname<br>Textile</h1>
            <p>Sistem Inventaris Gudang Kain Modern</p>
        </div>
        <div class="login-features">
            <div class="login-feature-item">
                <div class="fi-icon">📦</div>
                <div>
                    <div style="font-weight:600;font-size:14px">Manajemen Stok Real-time</div>
                    <div style="font-size:12px;opacity:.7">Pantau stok kain setiap saat</div>
                </div>
            </div>
            <div class="login-feature-item">
                <div class="fi-icon">📊</div>
                <div>
                    <div style="font-weight:600;font-size:14px">Laporan Komprehensif</div>
                    <div style="font-size:12px;opacity:.7">Analisis masuk &amp; keluar barang</div>
                </div>
            </div>
            <div class="login-feature-item">
                <div class="fi-icon">✅</div>
                <div>
                    <div style="font-weight:600;font-size:14px">Stock Opname Digital</div>
                    <div style="font-size:12px;opacity:.7">Pengecekan stok fisik mudah</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL -->
    <div class="login-right">
        <div class="login-form-wrap">
            <h2>Selamat Datang 👋</h2>
            <p>Masuk ke sistem inventaris gudang textile</p>

            <?php if ($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Username <span style="color:var(--danger)">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" name="username" class="form-control"
                               placeholder="Masukkan username..."
                               value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                               required autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <label>Password <span style="color:var(--danger)">*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" id="pwdInput" class="form-control"
                               placeholder="Masukkan password..." required>
                        <button type="button" onclick="togglePwd()"
                            style="position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);font-size:16px;padding:4px">
                            <i class="fas fa-eye" id="pwdIcon"></i>
                        </button>
                    </div>
                </div>
                <button type="submit" class="btn btn-primary btn-block btn-lg" style="margin-top:8px">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Sistem
                </button>
            </form>

            <div style="margin-top:28px;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid var(--border)">
                <p style="font-size:12px;color:var(--text-muted);font-weight:700;margin-bottom:6px">
                    <i class="fas fa-info-circle" style="color:var(--accent)"></i> Default Login:
                </p>
                <p style="font-size:13px;color:var(--text-main)">
                    Admin &nbsp;: <strong>admin</strong> / <strong>password</strong><br>
                    User &nbsp;&nbsp;&nbsp;: <strong>user1</strong> / <strong>password</strong>
                </p>
            </div>
            <p style="margin-top:20px;font-size:12px;color:var(--text-light);text-align:center">
                &copy; <?= date('Y') ?> Stock Opname Textile System
            </p>
        </div>
    </div>
</div>

<script>
window.addEventListener('load', () => {
    setTimeout(() => {
        const l = document.getElementById('pageLoader');
        if (l) { l.classList.add('hidden'); setTimeout(()=>l.remove(),500); }
    }, 300);
});
function togglePwd() {
    const i = document.getElementById('pwdInput');
    const ic = document.getElementById('pwdIcon');
    if (i.type==='password') { i.type='text'; ic.className='fas fa-eye-slash'; }
    else { i.type='password'; ic.className='fas fa-eye'; }
}
</script>
</body>
</html>