<?php
// 1. TAMBAHKAN INI UNTUK MELACAK ERROR JIKA HALAMAN PUTIH
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config/koneksi.php';

// Redirect kalau sudah login
if (isset($_SESSION['user_id'])) {
    header('Location: index.php'); 
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if (empty($username) || empty($password)) {
        $error = 'Username dan password wajib diisi!';
    } else {
        // Gunakan try-catch agar jika database error, tidak langsung putih polos
        try {
            $stmt = $conn->prepare("SELECT * FROM admin WHERE username = ? AND status = 'aktif' LIMIT 1");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                // Set Session
                $_SESSION['user_id']      = $user['id'];
                $_SESSION['username']     = $user['username'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['role']         = $user['role'];
                $_SESSION['foto']         = $user['foto'];
                
                header('Location: index.php'); 
                exit;
            } else {
                $error = 'Username atau password salah!';
            }
        } catch (Exception $e) {
            $error = "Terjadi kesalahan sistem: " . $e->getMessage();
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
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>

<div class="page-loader" id="pageLoader">
    <div class="loader-spinner"></div>
</div>

<div class="login-page">
    <!-- LEFT PANEL (Visual) -->
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
                    <div style="font-size:12px;opacity:.7">Analisis masuk & keluar barang</div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT PANEL (Form) -->
    <div class="login-right">
        <div class="login-form-wrap">
            <h2>Selamat Datang 👋</h2>
            <p>Masuk ke sistem inventaris gudang textile</p>

            <?php if ($error): ?>
            <div class="alert alert-danger" style="background:#fee2e2; color:#dc2626; padding:12px; border-radius:8px; margin-bottom:15px; font-size:14px;">
                <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group" style="margin-bottom:15px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Username</label>
                    <div class="input-wrap" style="position:relative;">
                        <i class="fas fa-user" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                        <input type="text" name="username" class="form-control" 
                               style="width:100%; padding:10px 10px 10px 40px; border:1px solid #ddd; border-radius:8px;"
                               placeholder="Masukkan username..." 
                               value="<?= htmlspecialchars($username ?? '') ?>" required autofocus>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom:20px;">
                    <label style="display:block; margin-bottom:5px; font-weight:600;">Password</label>
                    <div class="input-wrap" style="position:relative;">
                        <i class="fas fa-lock" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8;"></i>
                        <input type="password" name="password" id="password" class="form-control" 
                               style="width:100%; padding:10px 40px 10px 40px; border:1px solid #ddd; border-radius:8px;"
                               placeholder="Masukkan password..." required>
                        <button type="button" onclick="togglePassword('password')" 
                                style="position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#94a3b8;">
                            <i class="fas fa-eye" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary" style="width:100%; padding:12px; background:#2563eb; color:white; border:none; border-radius:8px; font-weight:bold; cursor:pointer;">
                    <i class="fas fa-sign-in-alt"></i> Masuk ke Sistem
                </button>
            </form>

            <div style="margin-top:28px;padding:16px;background:#f8fafc;border-radius:10px;border:1px solid #e2e8f0">
                <p style="font-size:12px;color:#64748b;font-weight:600;margin-bottom:8px">
                    <i class="fas fa-info-circle"></i> Catatan:
                </p>
                <p style="font-size:12px;color:#64748b">
                    Pastikan status akun Anda <strong>Aktif</strong> untuk dapat masuk ke sistem.
                </p>
            </div>
        </div>
    </div>
</div>

<script>
// Hilangkan loader
window.addEventListener('load', () => {
    const l = document.getElementById('pageLoader');
    if(l) {
        l.style.display = 'none';
    }
});

// Toggle Password
function togglePassword(id) {
    const inp = document.getElementById(id);
    const icon = document.getElementById('eyeIcon');
    if (inp.type === 'password') {
        inp.type = 'text'; 
        icon.className = 'fas fa-eye-slash';
    } else {
        inp.type = 'password'; 
        icon.className = 'fas fa-eye';
    }
}
</script>
</body>
</html>