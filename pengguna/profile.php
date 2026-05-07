<?php
require_once '../config/auth.php'; // Di sini $BASE seharusnya sudah dibuat
require_once '../config/koneksi.php';
global $BASE;
// Pastikan variabel $BASE tersedia (Jika di auth.php belum diperbaiki)
if (!isset($BASE)) {
    $BASE = '/STOKOPNAMETEXTILE/'; 
}

$page_title = 'Profil Saya';

$id   = (int)$_SESSION['user_id'];
$user = $conn->query("SELECT * FROM admin WHERE id=$id")->fetch_assoc();

$pesan = $error = '';

// Update profil
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi'])) {

    if ($_POST['aksi'] === 'update_profil') {
        $nama   = esc($conn, trim($_POST['nama_lengkap']));
        $email  = esc($conn, trim($_POST['email']));
        $telp   = esc($conn, trim($_POST['telepon']));

        // Upload foto baru
        $foto = $user['foto'];
        if (!empty($_FILES['foto']['name'])) {
            $file_size = $_FILES['foto']['size'];
            $ext = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
            
            // Validasi format dan ukuran (Maks 2MB)
            if (!in_array($ext, ['jpg','jpeg','png','webp'])) {
                $error = 'Format foto harus JPG, PNG, atau WEBP!';
            } elseif ($file_size > 2 * 1024 * 1024) {
                $error = 'Ukuran foto maksimal 2MB!';
            } else {
                $nama_foto = 'user_' . $id . '_' . time() . '.' . $ext;
                $dest = '../gambar/' . $nama_foto;
                
                // Pastikan folder gambar ada
                if (!is_dir('../gambar')) mkdir('../gambar', 0777, true);

                if (move_uploaded_file($_FILES['foto']['tmp_name'], $dest)) {
                    // Hapus foto lama jika bukan default
                    if ($foto && $foto !== 'default.png' && file_exists('../gambar/' . $foto)) {
                        @unlink('../gambar/' . $foto);
                    }
                    $foto = $nama_foto;
                } else {
                    $error = 'Gagal mengunggah foto ke server.';
                }
            }
        }

        if (!$error) {
            $sql = "UPDATE admin SET nama_lengkap='$nama', email='$email', telepon='$telp', foto='$foto' WHERE id=$id";
            if ($conn->query($sql)) {
                $_SESSION['nama_lengkap'] = $nama;
                $_SESSION['foto']         = $foto;
                $pesan = 'Profil berhasil diperbarui!';
                // Refresh data user
                $user = $conn->query("SELECT * FROM admin WHERE id=$id")->fetch_assoc();
            } else { 
                $error = 'Gagal update database: ' . $conn->error; 
            }
        }
    }

    if ($_POST['aksi'] === 'ganti_password') {
        $lama    = $_POST['password_lama'];
        $baru    = $_POST['password_baru'];
        $konfirm = $_POST['password_konfirm'];

        if ($baru !== $konfirm) {
            $error = 'Konfirmasi password baru tidak cocok!';
        } elseif (strlen($baru) < 6) {
            $error = 'Password baru minimal 6 karakter!';
        } elseif (!password_verify($lama, $user['password'])) {
            // Jika kamu masih pakai password tanpa hash (MD5/Plain), tambahkan pengecekan ini:
            if ($lama !== $user['password']) {
                $error = 'Password lama tidak benar!';
            }
        }

        if (!$error) {
            $hash = password_hash($baru, PASSWORD_DEFAULT);
            if ($conn->query("UPDATE admin SET password='$hash' WHERE id=$id")) {
                $pesan = 'Password berhasil diubah!';
                // Refresh data user untuk mendapatkan password hash terbaru
                $user = $conn->query("SELECT * FROM admin WHERE id=$id")->fetch_assoc();
            } else { 
                $error = 'Gagal ubah password!'; 
            }
        }
    }
}

// Statistik aktivitas (Cek jika data null)
$jml_masuk  = $conn->query("SELECT COUNT(*) as j FROM transaksi_masuk WHERE id_admin=$id")->fetch_assoc()['j'] ?? 0;
$jml_keluar = $conn->query("SELECT COUNT(*) as j FROM transaksi_keluar WHERE id_admin=$id")->fetch_assoc()['j'] ?? 0;
$jml_opname = $conn->query("SELECT COUNT(*) as j FROM stock_opname WHERE id_admin=$id")->fetch_assoc()['j'] ?? 0;

include '../header.php';
?>

<?php if ($pesan): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlspecialchars($pesan); ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= htmlspecialchars($error); ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:300px 1fr;gap:24px;align-items:start">

    <div style="display:flex;flex-direction:column;gap:20px">
        <div class="card" style="text-align:center;padding:32px 24px">
            <div style="position:relative;display:inline-block;margin-bottom:16px">
                <img id="fotoPreview"
                     src="<?= $BASE; ?>gambar/<?= htmlspecialchars($user['foto'] ?? 'default.png'); ?>"
                     onerror="this.src='<?= $BASE; ?>gambar/default.png'"
                     style="width:120px;height:120px;border-radius:50%;object-fit:cover;border:4px solid var(--accent);box-shadow:0 8px 24px rgba(200,151,58,.3)">
                <label for="fotoInput" style="position:absolute;bottom:4px;right:4px;width:30px;height:30px;background:var(--accent);border-radius:50%;display:flex;align-items:center;justify-content:center;cursor:pointer;box-shadow:0 2px 8px rgba(0,0,0,.2)">
                    <i class="fas fa-camera" style="color:#fff;font-size:12px"></i>
                </label>
            </div>
            <h3 style="font-size:18px;font-weight:800;margin-bottom:4px"><?= htmlspecialchars($user['nama_lengkap']); ?></h3>
            <p style="color:var(--text-muted);font-size:13px">@<?= htmlspecialchars($user['username']); ?></p>
            
            <div style="margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
                <div style="display:flex;justify-content:space-around;gap:8px">
                    <div style="text-align:center">
                        <div style="font-size:22px;font-weight:800;color:var(--success)"><?= $jml_masuk; ?></div>
                        <div style="font-size:10px;color:var(--text-muted)">Masuk</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:22px;font-weight:800;color:var(--danger)"><?= $jml_keluar; ?></div>
                        <div style="font-size:10px;color:var(--text-muted)">Keluar</div>
                    </div>
                    <div style="text-align:center">
                        <div style="font-size:22px;font-weight:800;color:var(--accent)"><?= $jml_opname; ?></div>
                        <div style="font-size:10px;color:var(--text-muted)">Opname</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header"><h3>Ganti Password</h3></div>
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="aksi" value="ganti_password">
                    <div class="form-group">
                        <label>Password Lama</label>
                        <input type="password" name="password_lama" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Password Baru</label>
                        <input type="password" name="password_baru" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Konfirmasi Password</label>
                        <input type="password" name="password_konfirm" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-danger btn-block">Simpan Password</button>
                </form>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="update_profil">
                <input type="file" id="fotoInput" name="foto" accept="image/*" style="display:none" onchange="previewFoto(this)">
                
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                    <div class="form-group">
                        <label>Nama Lengkap</label>
                        <input type="text" name="nama_lengkap" class="form-control" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Email</label>
                        <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Telepon</label>
                        <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($user['telepon'] ?? ''); ?>">
                    </div>
                </div>

                <div style="display:flex;gap:10px;margin-top:24px;">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="<?= $BASE; ?>index.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function previewFoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('fotoPreview').src = e.target.result;
            if(document.getElementById('fotoPreview2')) {
                document.getElementById('fotoPreview2').src = e.target.result;
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>

<?php include '../footer.php'; ?>