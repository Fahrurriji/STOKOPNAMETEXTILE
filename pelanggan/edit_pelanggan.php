<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
cek_admin();
$page_title = 'Edit Pelanggan';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: kelola_pelanggan.php'); exit; }
$row = $conn->query("SELECT * FROM pelanggan WHERE id=$id")->fetch_assoc();
if (!$row) { header('Location: kelola_pelanggan.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode  = strtoupper(trim(esc($conn, $_POST['kode_pelanggan'])));
    $nama  = esc($conn, $_POST['nama_pelanggan']);
    $alamat= esc($conn, $_POST['alamat']);
    $telp  = esc($conn, $_POST['telepon']);
    $email = esc($conn, $_POST['email']);
    if ($conn->query("UPDATE pelanggan SET kode_pelanggan='$kode',nama_pelanggan='$nama',alamat='$alamat',telepon='$telp',email='$email' WHERE id=$id")) {
        $_SESSION['pesan'] = "Pelanggan berhasil diperbarui!";
        header('Location: kelola_pelanggan.php'); exit;
    } else { $error = 'Gagal: ' . $conn->error; }
}
include '../header.php';
?>
<?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<div style="max-width:700px;margin:0 auto"><div class="card">
  <div class="card-header"><div class="card-header-icon"><i class="fas fa-edit"></i></div><div><h3>Edit Pelanggan</h3></div></div>
  <div class="card-body"><form method="POST">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div class="form-group"><label>Kode Pelanggan</label><div class="input-wrap"><i class="fas fa-barcode input-icon"></i><input type="text" name="kode_pelanggan" class="form-control" value="<?= htmlspecialchars($row['kode_pelanggan']) ?>" style="text-transform:uppercase"></div></div>
      <div class="form-group"><label>Nama Pelanggan <span style="color:red">*</span></label><div class="input-wrap"><i class="fas fa-user input-icon"></i><input type="text" name="nama_pelanggan" class="form-control" value="<?= htmlspecialchars($row['nama_pelanggan']) ?>" required></div></div>
      <div class="form-group"><label>Telepon</label><div class="input-wrap"><i class="fas fa-phone input-icon"></i><input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($row['telepon']??'') ?>"></div></div>
      <div class="form-group"><label>Email</label><div class="input-wrap"><i class="fas fa-envelope input-icon"></i><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']??'') ?>"></div></div>
      <div class="form-group" style="grid-column:span 2"><label>Alamat</label><textarea name="alamat" class="form-control no-icon" rows="3"><?= htmlspecialchars($row['alamat']??'') ?></textarea></div>
    </div>
    <div style="display:flex;gap:10px;margin-top:12px;padding-top:16px;border-top:1px solid var(--border)">
      <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update</button>
      <a href="kelola_pelanggan.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
    </div>
  </form></div>
</div></div>
<?php include '../footer.php';
