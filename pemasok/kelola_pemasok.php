<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
cek_admin();
$page_title = 'Kelola Pemasok';
if (isset($_SESSION['pesan'])) { $pesan = $_SESSION['pesan']; unset($_SESSION['pesan']); }
if (isset($_SESSION['error'])) { $error = $_SESSION['error']; unset($_SESSION['error']); }
$search = esc($conn, $_GET['search'] ?? '');
$where = $search ? "WHERE nama_pemasok LIKE '%$search%' OR kode_pemasok LIKE '%$search%'" : '';
$data = $conn->query("SELECT * FROM pemasok $where ORDER BY id DESC");
include '../header.php';
?>
<?php if (isset($pesan)): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>
<div class="card">
  <div class="card-header">
    <div class="card-header-icon"><i class="fas fa-truck"></i></div>
    <div><h3>Data Pemasok / Supplier</h3><p>Daftar supplier kain dan bahan textile</p></div>
    <a href="tambah_pemasok.php" class="btn btn-primary btn-sm"><i class="fas fa-plus"></i> Tambah Pemasok</a>
  </div>
  <div class="page-toolbar">
    <form method="GET" style="display:flex;gap:10px">
      <div class="search-box"><i class="fas fa-search"></i>
        <input type="text" name="search" placeholder="Cari pemasok..." value="<?= htmlspecialchars($search) ?>">
      </div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
      <a href="kelola_pemasok.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
    </form>
    <span style="font-size:13px;color:var(--text-muted)"><?= $data->num_rows ?> pemasok</span>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>No</th><th>Kode</th><th>Nama Pemasok</th><th>Kontak Person</th><th>Telepon</th><th>Email</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if ($data->num_rows === 0): ?>
        <tr><td colspan="7"><div class="empty-state"><div class="es-icon">🚚</div><h4>Belum ada pemasok</h4><p>Tambah pemasok baru untuk mulai mencatat transaksi pembelian</p></div></td></tr>
      <?php else: $no=1; while ($r = $data->fetch_assoc()): ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><span class="badge badge-primary"><?= htmlspecialchars($r['kode_pemasok']) ?></span></td>
          <td><strong><?= htmlspecialchars($r['nama_pemasok']) ?></strong><br><small style="color:var(--text-muted)"><?= htmlspecialchars($r['alamat'] ?? '') ?></small></td>
          <td><?= htmlspecialchars($r['kontak_person'] ?? '-') ?></td>
          <td><?= htmlspecialchars($r['telepon'] ?? '-') ?></td>
          <td><?= htmlspecialchars($r['email'] ?? '-') ?></td>
          <td><div class="action-btns">
            <a href="edit_pemasok.php?id=<?= $r['id'] ?>" class="btn btn-warning btn-sm" data-tooltip="Edit"><i class="fas fa-edit"></i></a>
            <a href="#" onclick="confirmDelete('hapus_pemasok.php?id=<?= $r['id'] ?>','<?= htmlspecialchars($r['nama_pemasok']) ?>')" class="btn btn-danger btn-sm" data-tooltip="Hapus"><i class="fas fa-trash"></i></a>
          </div></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>
