<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';

$page_title = 'Laporan Stok Barang';

// Ambil data kategori untuk filter
$kategori_res = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");

// Logika Filter
$where = " WHERE 1=1 ";
if (!empty($_GET['id_kategori'])) {
    $id_kat = (int)$_GET['id_kategori'];
    $where .= " AND b.id_kategori = $id_kat ";
}
if (!empty($_GET['status'])) {
    if ($_GET['status'] == 'tipis') $where .= " AND b.stok <= 10 AND b.stok > 0 ";
    if ($_GET['status'] == 'habis') $where .= " AND b.stok <= 0 ";
}

// Query ambil data stok
$query = "SELECT b.*, k.nama_kategori 
          FROM barang b 
          LEFT JOIN kategori k ON b.id_kategori = k.id 
          $where 
          ORDER BY b.stok ASC";
$data = $conn->query($query);

include '../header.php';
?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon" style="background:#f1f5f9;color:#475569">
            <i class="fas fa-boxes"></i>
        </div>
        <div>
            <h3>Laporan Stok Barang</h3>
            <p>Data persediaan kain di gudang saat ini</p>
        </div>
        <div style="margin-left:auto">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                <i class="fas fa-print"></i> Cetak Laporan
            </button>
        </div>
    </div>

    <div class="page-toolbar">
        <form method="GET" style="display:flex;gap:10px;flex-wrap:wrap">
            <select name="id_kategori" class="form-control" style="width:180px">
                <option value="">-- Semua Kategori --</option>
                <?php while($kat = $kategori_res->fetch_assoc()): ?>
                    <option value="<?= $kat['id'] ?>" <?= ($_GET['id_kategori'] ?? '') == $kat['id'] ? 'selected' : '' ?>>
                        <?= $kat['nama_kategori'] ?>
                    </option>
                <?php endwhile; ?>
            </select>
            
            <select name="status" class="form-control" style="width:150px">
                <option value="">-- Semua Status --</option>
                <option value="tipis" <?= ($_GET['status'] ?? '') == 'tipis' ? 'selected' : '' ?>>Stok Menipis</option>
                <option value="habis" <?= ($_GET['status'] ?? '') == 'habis' ? 'selected' : '' ?>>Stok Habis</option>
            </select>

            <button type="submit" class="btn btn-secondary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="laporan_stok.php" class="btn btn-light btn-sm">Reset</a>
        </form>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Nama Barang</th>
                    <th>Kategori</th>
                    <th style="text-align:right">Harga Beli</th>
                    <th style="text-align:center">Stok</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if($data->num_rows == 0): ?>
                    <tr><td colspan="7" style="text-align:center;padding:30px">Data tidak ditemukan.</td></tr>
                <?php else: $no=1; while($r = $data->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-info"><?= $r['kode_barang'] ?></span></td>
                    <td><strong><?= htmlspecialchars($r['nama_barang']) ?></strong></td>
                    <td><?= htmlspecialchars($r['nama_kategori'] ?? '-') ?></td>
                    <td style="text-align:right"><?= rupiah($r['harga_beli']) ?></td>
                    <td style="text-align:center"><strong><?= $r['stok'] ?></strong></td>
                    <td>
                        <?php if($r['stok'] <= 0): ?>
                            <span class="badge badge-danger">Habis</span>
                        <?php elseif($r['stok'] <= 10): ?>
                            <span class="badge badge-warning">Menipis</span>
                        <?php else: ?>
                            <span class="badge badge-success">Aman</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
@media print {
    .sidebar, .navbar, .page-toolbar, .btn, .card-header-icon { display: none !important; }
    .content { margin: 0; padding: 0; }
    .card { border: none; box-shadow: none; }
    .table { width: 100%; border-collapse: collapse; }
    .table th, .table td { border: 1px solid #ddd; padding: 8px; }
}
</style>

<?php include '../footer.php'; ?>