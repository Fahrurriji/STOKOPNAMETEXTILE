<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
cek_admin();
$page_title = 'Tambah Pelanggan';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode  = strtoupper(trim(esc($conn, $_POST['kode_pelanggan'])));
    if (empty($kode)) $kode = generate_kode($conn, 'pelanggan', 'kode_pelanggan', 'PLG');
    $nama  = esc($conn, $_POST['nama_pelanggan']);
    $alamat= esc($conn, $_POST['alamat']);
    $telp  = esc($conn, $_POST['telepon']);
    $email = esc($conn, $_POST['email']);
    if ($conn->query("INSERT INTO pelanggan (kode_pelanggan,nama_pelanggan,alamat,telepon,email) VALUES ('$kode','$nama','$alamat','$telp','$email')")) {
        $_SESSION['pesan'] = "Pelanggan <strong>$nama</strong> berhasil ditambahkan!";
        header('Location: kelola_pelanggan.php'); exit;
    } else { $error = 'Gagal: ' . $conn->error; }
}
$ka = generate_kode($conn, 'pelanggan', 'kode_pelanggan', 'PLG');
include '../header.php';
?>
<?php if (isset($error)): ?><div class="alert alert-danger"><?= $error ?></div><?php endif; ?>
<div style="max-width:700px;margin:0 auto"><div class="card">
  <div class="card-header"><div class="card-header-icon"><i class="fas fa-user-plus"></i></div><div><h3>Tambah Pelanggan Baru</h3></div></div>
  <div class="card-body"><form method="POST">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div class="form-group"><label>Kode Pelanggan</label><div class="input-wrap"><i class="fas fa-barcode input-icon"></i><input type="text" name="kode_pelanggan" class="form-control" placeholder="<?= $ka ?>" style="text-transform:uppercase"></div></div>
      <div class="form-group"><label>Nama Pelanggan <span style="color:red">*</span></label><div class="input-wrap"><i class="fas fa-user input-icon"></i><input type="text" name="nama_pelanggan" class="form-control" required></div></div>
      <div class="form-group"><label>Telepon</label><div class="input-wrap"><i class="fas fa-phone input-icon"></i><input type="text" name="telepon" class="form-control"></div></div>
      <div class="form-group"><label>Email</label><div class="input-wrap"><i class="fas fa-envelope input-icon"></i><input type="email" name="email" class="form-control"></div></div>
      <div class="form-group" style="grid-column:span 2"><label>Alamat</label><textarea name="alamat" class="form-control no-icon" rows="3"></textarea></div>
    </div>
    <div style="display:flex;gap:10px;margin-top:12px;padding-top:16px;border-top:1px solid var(--border)">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
      <a href="kelola_pelanggan.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
    </div>
  </form></div>
</div></div>
<?php include '../footer.php'; ?>
