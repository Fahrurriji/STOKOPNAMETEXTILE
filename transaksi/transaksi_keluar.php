<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Transaksi Barang Keluar';
if (isset($_SESSION['pesan'])) { $pesan = $_SESSION['pesan']; unset($_SESSION['pesan']); }
if (isset($_SESSION['error'])) { $error = $_SESSION['error']; unset($_SESSION['error']); }
$tgl_awal  = $_GET['tgl_awal'] ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$data = $conn->query("SELECT tk.*, pl.nama_pelanggan, a.nama_lengkap as nama_admin
    FROM transaksi_keluar tk
    LEFT JOIN pelanggan pl ON tk.id_pelanggan = pl.id
    LEFT JOIN admin a ON tk.id_admin = a.id
    WHERE tk.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'
    ORDER BY tk.id DESC");
include '../header.php';
?>
<?php if (isset($pesan)): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>
<div class="card">
  <div class="card-header">
    <div class="card-header-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-arrow-circle-up"></i></div>
    <div><h3>Transaksi Barang Keluar</h3><p>Penjualan kain kepada pelanggan</p></div>
    <a href="tambah_keluar.php" class="btn btn-danger btn-sm"><i class="fas fa-plus"></i> Tambah Transaksi</a>
  </div>
  <div class="page-toolbar">
    <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap;align-items:flex-end">
      <div><label style="font-size:11px;display:block;margin-bottom:3px">Dari</label>
        <input type="date" name="tgl_awal" class="form-control no-icon" style="padding:8px 12px;width:160px" value="<?= $tgl_awal ?>">
      </div>
      <div><label style="font-size:11px;display:block;margin-bottom:3px">Sampai</label>
        <input type="date" name="tgl_akhir" class="form-control no-icon" style="padding:8px 12px;width:160px" value="<?= $tgl_akhir ?>">
      </div>
      <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
    </form>
    <span style="font-size:13px;color:var(--text-muted);margin-left:auto"><?= $data->num_rows ?> transaksi</span>
  </div>
  <div class="table-wrap">
    <table class="table">
      <thead><tr><th>No</th><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Total Item</th><th>Diskon</th><th>Total Bayar</th><th>Admin</th><th>Aksi</th></tr></thead>
      <tbody>
      <?php if ($data->num_rows === 0): ?>
        <tr><td colspan="9"><div class="empty-state"><div class="es-icon">🛒</div><h4>Tidak ada transaksi keluar</h4></div></td></tr>
      <?php else: $no=1; $tot=0; while ($r = $data->fetch_assoc()): $tot += $r['total_bayar']; ?>
        <tr>
          <td><?= $no++ ?></td>
          <td><span class="badge badge-danger"><?= htmlspecialchars($r['no_transaksi']) ?></span></td>
          <td><?= tgl_indo($r['tanggal']) ?></td>
          <td><?= htmlspecialchars($r['nama_pelanggan'] ?? 'Umum') ?></td>
          <td><span class="badge badge-primary"><?= $r['total_item'] ?> item</span></td>
          <td><?= $r['diskon'] > 0 ? $r['diskon'].'%' : '-' ?></td>
          <td><strong style="color:var(--success)"><?= rupiah($r['total_bayar']) ?></strong></td>
          <td style="font-size:12px"><?= htmlspecialchars($r['nama_admin']) ?></td>
          <td><div class="action-btns">
            <a href="detail_keluar.php?id=<?= $r['id'] ?>" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i></a>
            <a href="#" onclick="confirmDelete('hapus_keluar.php?id=<?= $r['id'] ?>','<?= htmlspecialchars($r['no_transaksi']) ?>')" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></a>
          </div></td>
        </tr>
      <?php endwhile; ?>
        <tr style="background:#f8fafc;font-weight:700">
          <td colspan="6" style="text-align:right;padding:14px 16px">TOTAL PENJUALAN:</td>
          <td style="color:var(--success)"><?= rupiah($tot) ?></td>
          <td colspan="2"></td>
        </tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../footer.php'; ?>