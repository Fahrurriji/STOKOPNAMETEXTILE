<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
cek_admin();
$page_title = 'Tambah Pemasok';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode   = strtoupper(trim(esc($conn, $_POST['kode_pemasok'])));
    if (empty($kode)) $kode = generate_kode($conn, 'pemasok', 'kode_pemasok', 'PMS');
    $nama   = esc($conn, $_POST['nama_pemasok']);
    $alamat = esc($conn, $_POST['alamat']);
    $telp   = esc($conn, $_POST['telepon']);
    $email  = esc($conn, $_POST['email']);
    $kontak = esc($conn, $_POST['kontak_person']);
    $cek = $conn->query("SELECT id FROM pemasok WHERE kode_pemasok='$kode'");
    if ($cek->num_rows > 0) { $error = 'Kode pemasok sudah ada!'; }
    else {
        if ($conn->query("INSERT INTO pemasok (kode_pemasok,nama_pemasok,alamat,telepon,email,kontak_person) VALUES ('$kode','$nama','$alamat','$telp','$email','$kontak')")) {
            $_SESSION['pesan'] = "Pemasok <strong>$nama</strong> berhasil ditambahkan!";
            header('Location: kelola_pemasok.php'); exit;
        } else { $error = 'Gagal: ' . $conn->error; }
    }
}
$kode_auto = generate_kode($conn, 'pemasok', 'kode_pemasok', 'PMS');
include '../header.php';
?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>
<div style="max-width:700px;margin:0 auto"><div class="card">
  <div class="card-header"><div class="card-header-icon"><i class="fas fa-truck"></i></div><div><h3>Tambah Pemasok Baru</h3></div></div>
  <div class="card-body"><form method="POST">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
      <div class="form-group"><label>Kode Pemasok</label><div class="input-wrap"><i class="fas fa-barcode input-icon"></i><input type="text" name="kode_pemasok" class="form-control" placeholder="<?= $kode_auto ?>" style="text-transform:uppercase"></div></div>
      <div class="form-group"><label>Nama Pemasok <span style="color:red">*</span></label><div class="input-wrap"><i class="fas fa-building input-icon"></i><input type="text" name="nama_pemasok" class="form-control" required></div></div>
      <div class="form-group"><label>Kontak Person</label><div class="input-wrap"><i class="fas fa-user input-icon"></i><input type="text" name="kontak_person" class="form-control"></div></div>
      <div class="form-group"><label>Telepon</label><div class="input-wrap"><i class="fas fa-phone input-icon"></i><input type="text" name="telepon" class="form-control"></div></div>
      <div class="form-group"><label>Email</label><div class="input-wrap"><i class="fas fa-envelope input-icon"></i><input type="email" name="email" class="form-control"></div></div>
      <div class="form-group"><label>Alamat</label><textarea name="alamat" class="form-control no-icon" rows="3" placeholder="Alamat lengkap..."></textarea></div>
    </div>
    <div style="display:flex;gap:10px;margin-top:12px;padding-top:16px;border-top:1px solid var(--border)">
      <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Pemasok</button>
      <a href="kelola_pemasok.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
    </div>
  </form></div>
</div></div>
<?php include '../footer.php'; ?>
