<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Edit Pemasok';
$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: kelola_pemasok.php'); exit; }
$row = $conn->query("SELECT * FROM pemasok WHERE id=$id")->fetch_assoc();
if (!$row) { header('Location: kelola_pemasok.php'); exit; }
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode   = strtoupper(trim(esc($conn, $_POST['kode_pemasok'])));
    $nama   = esc($conn, $_POST['nama_pemasok']);
    $alamat = esc($conn, $_POST['alamat']);
    $telp   = esc($conn, $_POST['telepon']);
    $email  = esc($conn, $_POST['email']);
    $kontak = esc($conn, $_POST['kontak_person']);
    $cek = $conn->query("SELECT id FROM pemasok WHERE kode_pemasok='$kode' AND id!=$id");
    if ($cek->num_rows > 0) { $error = 'Kode sudah digunakan!'; }
    elseif ($conn->query("UPDATE pemasok SET kode_pemasok='$kode',nama_pemasok='$nama',alamat='$alamat',telepon='$telp',email='$email',kontak_person='$kontak' WHERE id=$id")) {
        $_SESSION['pesan'] = "Pemasok berhasil diperbarui!";
        header('Location: kelola_pemasok.php'); exit;
    } else { $error = 'Gagal: ' . $conn->error; }
}
include '../header.php';
?>
<?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<div style="max-width:700px;margin:0 auto"><div class="card">
  <div class="card-header"><div class="card-header-icon"><i class="fas fa-edit"></i></div><div><h3>Edit Pemasok</h3><p><?= htmlspecialchars($row['nama_pemasok']) ?></p></div></div>
  <div class="card-body"><form method="POST">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div class="form-group"><label>Kode Pemasok</label><div class="input-wrap"><i class="fas fa-barcode input-icon"></i><input type="text" name="kode_pemasok" class="form-control" value="<?= htmlspecialchars($row['kode_pemasok']) ?>" style="text-transform:uppercase"></div></div>
      <div class="form-group"><label>Nama Pemasok <span style="color:red">*</span></label><div class="input-wrap"><i class="fas fa-building input-icon"></i><input type="text" name="nama_pemasok" class="form-control" value="<?= htmlspecialchars($row['nama_pemasok']) ?>" required></div></div>
      <div class="form-group"><label>Kontak Person</label><div class="input-wrap"><i class="fas fa-user input-icon"></i><input type="text" name="kontak_person" class="form-control" value="<?= htmlspecialchars($row['kontak_person']??'') ?>"></div></div>
      <div class="form-group"><label>Telepon</label><div class="input-wrap"><i class="fas fa-phone input-icon"></i><input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($row['telepon']??'') ?>"></div></div>
      <div class="form-group"><label>Email</label><div class="input-wrap"><i class="fas fa-envelope input-icon"></i><input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']??'') ?>"></div></div>
      <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control no-icon" rows="3"><?= htmlspecialchars($row['alamat']??'') ?></textarea></div>
    </div>
    <div style="display:flex;gap:10px;margin-top:12px;padding-top:16px;border-top:1px solid var(--border)">
      <button type="submit" class="btn btn-warning"><i class="fas fa-save"></i> Update Pemasok</button>
      <a href="kelola_pemasok.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
    </div>
  </form></div>
</div></div>
<?php include '../footer.php'; ?>