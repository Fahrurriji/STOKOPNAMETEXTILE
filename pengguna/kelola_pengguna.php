<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
cek_admin(); // Hanya admin
$page_title = 'Kelola Pengguna';

if (isset($_SESSION['pesan'])) { $pesan = $_SESSION['pesan']; unset($_SESSION['pesan']); }
if (isset($_SESSION['error'])) { $error = $_SESSION['error']; unset($_SESSION['error']); }

// Proses Tambah/Edit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi     = $_POST['aksi'] ?? '';
    $id_edit  = (int)($_POST['id_edit'] ?? 0);
    $nama     = esc($conn, trim($_POST['nama_lengkap']));
    $username = esc($conn, trim($_POST['username']));
    $email    = esc($conn, trim($_POST['email']));
    $telp     = esc($conn, trim($_POST['telepon']));
    $role     = esc($conn, $_POST['role']);
    $status   = esc($conn, $_POST['status']);

    if ($aksi === 'tambah') {
        $pwd = $_POST['password'];
        if (empty($pwd)) { $error = 'Password wajib diisi!'; }
        else {
            $hash = password_hash($pwd, PASSWORD_DEFAULT);
            $cek  = $conn->query("SELECT id FROM admin WHERE username='$username' OR email='$email'");
            if ($cek->num_rows > 0) { $error = 'Username atau email sudah digunakan!'; }
            elseif ($conn->query("INSERT INTO admin (username,password,nama_lengkap,email,telepon,role,status) VALUES ('$username','$hash','$nama','$email','$telp','$role','$status')")) {
                $_SESSION['pesan'] = "Pengguna <strong>$nama</strong> berhasil ditambahkan!";
                header('Location: kelola_pengguna.php'); exit;
            } else { $error = 'Gagal: ' . $conn->error; }
        }
    }

    if ($aksi === 'edit' && $id_edit) {
        // Jangan ubah role/status diri sendiri
        if ($id_edit == $_SESSION['user_id']) { $error = 'Tidak dapat mengedit akun Anda sendiri dari sini. Gunakan halaman Profil.'; }
        else {
            $pwd_sql = '';
            if (!empty($_POST['password'])) {
                $hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
                $pwd_sql = ", password='$hash'";
            }
            $cek = $conn->query("SELECT id FROM admin WHERE (username='$username' OR email='$email') AND id!=$id_edit");
            if ($cek->num_rows > 0) { $error = 'Username atau email sudah digunakan!'; }
            elseif ($conn->query("UPDATE admin SET nama_lengkap='$nama',username='$username',email='$email',telepon='$telp',role='$role',status='$status'$pwd_sql WHERE id=$id_edit")) {
                $_SESSION['pesan'] = "Pengguna <strong>$nama</strong> berhasil diperbarui!";
                header('Location: kelola_pengguna.php'); exit;
            } else { $error = 'Gagal: ' . $conn->error; }
        }
    }
}

// Proses nonaktifkan/aktifkan
if (isset($_GET['toggle']) && isset($_GET['id'])) {
    $tid = (int)$_GET['id'];
    if ($tid == $_SESSION['user_id']) {
        $_SESSION['error'] = 'Tidak dapat menonaktifkan akun Anda sendiri!';
    } else {
        $u = $conn->query("SELECT status, nama_lengkap FROM admin WHERE id=$tid")->fetch_assoc();
        $new_status = $u['status'] === 'aktif' ? 'nonaktif' : 'aktif';
        $conn->query("UPDATE admin SET status='$new_status' WHERE id=$tid");
        $_SESSION['pesan'] = "Status <strong>{$u['nama_lengkap']}</strong> diubah ke <strong>$new_status</strong>.";
    }
    header('Location: kelola_pengguna.php'); exit;
}

$data = $conn->query("SELECT * FROM admin ORDER BY role DESC, id ASC");
// Data untuk edit modal
$edit_user = null;
if (isset($_GET['edit'])) {
    $edit_id  = (int)$_GET['edit'];
    $edit_user = $conn->query("SELECT * FROM admin WHERE id=$edit_id")->fetch_assoc();
}

include '../header.php';
?>
<?php if (isset($pesan)): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:1fr 360px;gap:24px;align-items:start">

    <!-- TABEL PENGGUNA -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-user-shield"></i></div>
            <div><h3>Daftar Pengguna</h3><p>Kelola akun admin dan staff gudang</p></div>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th>No</th><th>Nama</th><th>Username</th><th>Email</th><th>Role</th><th>Status</th><th>Aksi</th></tr></thead>
                <tbody>
                <?php $no=1; while ($r = $data->fetch_assoc()): ?>
                <tr <?= $r['id'] == $_SESSION['user_id'] ? 'style="background:rgba(200,151,58,.06)"' : '' ?>>
                    <td><?= $no++ ?></td>
                    <td>
                        <div style="display:flex;align-items:center;gap:10px">
                            <div style="width:34px;height:34px;border-radius:50%;background:var(--primary-dark);display:flex;align-items:center;justify-content:center;color:var(--accent);font-weight:700;font-size:14px;flex-shrink:0">
                                <?= strtoupper(substr($r['nama_lengkap'],0,1)) ?>
                            </div>
                            <div>
                                <div style="font-weight:600"><?= htmlspecialchars($r['nama_lengkap']) ?></div>
                                <?php if ($r['id'] == $_SESSION['user_id']): ?>
                                <div style="font-size:10px;color:var(--accent)">← Akun Anda</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td><code style="background:#f8fafc;padding:2px 6px;border-radius:4px;font-size:12px"><?= htmlspecialchars($r['username']) ?></code></td>
                    <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($r['email']) ?></td>
                    <td>
                        <span class="badge <?= $r['role']==='admin' ? 'badge-danger' : 'badge-primary' ?>">
                            <?= $r['role']==='admin' ? '👑 Admin' : '👤 Staff' ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($r['id'] != $_SESSION['user_id']): ?>
                        <a href="kelola_pengguna.php?toggle=1&id=<?= $r['id'] ?>" onclick="return confirm('Ubah status <?= htmlspecialchars($r['nama_lengkap']) ?>?')">
                        <?php endif; ?>
                        <span class="badge <?= $r['status']==='aktif' ? 'badge-success' : 'badge-muted' ?>">
                            <?= $r['status']==='aktif' ? '✓ Aktif' : '✗ Nonaktif' ?>
                        </span>
                        <?php if ($r['id'] != $_SESSION['user_id']): ?></a><?php endif; ?>
                    </td>
                    <td>
                        <div class="action-btns">
                            <a href="kelola_pengguna.php?edit=<?= $r['id'] ?>" class="btn btn-warning btn-sm" data-tooltip="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- FORM TAMBAH / EDIT -->
    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-<?= $edit_user ? 'edit' : 'user-plus' ?>"></i></div>
            <div><h3><?= $edit_user ? 'Edit Pengguna' : 'Tambah Pengguna Baru' ?></h3></div>
            <?php if ($edit_user): ?>
            <a href="kelola_pengguna.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="aksi" value="<?= $edit_user ? 'edit' : 'tambah' ?>">
                <?php if ($edit_user): ?>
                <input type="hidden" name="id_edit" value="<?= $edit_user['id'] ?>">
                <?php endif; ?>
                <div class="form-group">
                    <label>Nama Lengkap <span style="color:red">*</span></label>
                    <div class="input-wrap"><i class="fas fa-user input-icon"></i>
                        <input type="text" name="nama_lengkap" class="form-control" required
                               value="<?= htmlspecialchars($edit_user['nama_lengkap'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Username <span style="color:red">*</span></label>
                    <div class="input-wrap"><i class="fas fa-at input-icon"></i>
                        <input type="text" name="username" class="form-control" required
                               value="<?= htmlspecialchars($edit_user['username'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Email <span style="color:red">*</span></label>
                    <div class="input-wrap"><i class="fas fa-envelope input-icon"></i>
                        <input type="email" name="email" class="form-control" required
                               value="<?= htmlspecialchars($edit_user['email'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Telepon</label>
                    <div class="input-wrap"><i class="fas fa-phone input-icon"></i>
                        <input type="text" name="telepon" class="form-control"
                               value="<?= htmlspecialchars($edit_user['telepon'] ?? '') ?>">
                    </div>
                </div>
                <div class="form-group">
                    <label>Password <?= $edit_user ? '<span style="color:var(--text-muted);font-weight:400;font-size:11px">(kosong = tidak diubah)</span>' : '<span style="color:red">*</span>' ?></label>
                    <div class="input-wrap"><i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password" class="form-control"
                               placeholder="<?= $edit_user ? 'Kosongkan jika tidak diubah' : 'Minimal 6 karakter' ?>"
                               <?= $edit_user ? '' : 'required' ?>>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                    <div class="form-group">
                        <label>Role <span style="color:red">*</span></label>
                        <div class="input-wrap"><i class="fas fa-crown input-icon"></i>
                            <select name="role" class="form-control">
                                <option value="user"  <?= ($edit_user['role'] ?? 'user')==='user'  ? 'selected':'' ?>>👤 User/Staff</option>
                                <option value="admin" <?= ($edit_user['role'] ?? '')==='admin' ? 'selected':'' ?>>👑 Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div class="input-wrap"><i class="fas fa-toggle-on input-icon"></i>
                            <select name="status" class="form-control">
                                <option value="aktif"    <?= ($edit_user['status'] ?? 'aktif')==='aktif'    ? 'selected':'' ?>>Aktif</option>
                                <option value="nonaktif" <?= ($edit_user['status'] ?? '')==='nonaktif' ? 'selected':'' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div style="display:flex;gap:10px;margin-top:12px;padding-top:16px;border-top:1px solid var(--border)">
                    <button type="submit" class="btn <?= $edit_user ? 'btn-warning' : 'btn-primary' ?>">
                        <i class="fas fa-save"></i> <?= $edit_user ? 'Update Pengguna' : 'Tambah Pengguna' ?>
                    </button>
                    <?php if ($edit_user): ?>
                    <a href="kelola_pengguna.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>

</div>
<?php include '../footer.php'; ?>