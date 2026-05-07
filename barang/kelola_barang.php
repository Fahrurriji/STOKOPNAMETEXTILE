<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Kelola Produk / Barang';

if (isset($_SESSION['pesan'])) { $pesan = $_SESSION['pesan']; unset($_SESSION['pesan']); }
if (isset($_SESSION['error'])) { $error = $_SESSION['error']; unset($_SESSION['error']); }

$search  = esc($conn, $_GET['search'] ?? '');
$filter_kat = (int)($_GET['kategori'] ?? 0);
$filter  = $_GET['filter'] ?? '';

$where = "WHERE b.status = 'aktif'";
if ($search) $where .= " AND (b.nama_barang LIKE '%$search%' OR b.kode_barang LIKE '%$search%')";
if ($filter_kat) $where .= " AND b.id_kategori=$filter_kat";
if ($filter === 'minim') $where .= " AND b.stok <= b.stok_minimum";

$sql = "SELECT b.*, k.nama_kategori, p.nama_pemasok FROM barang b
        LEFT JOIN kategori k ON b.id_kategori = k.id
        LEFT JOIN pemasok p ON b.id_pemasok = p.id
        $where ORDER BY b.id DESC";
$data = $conn->query($sql);

$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");

include '../header.php';
?>

<?php if (isset($pesan)): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>
<?php if ($filter === 'minim'): ?>
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Menampilkan produk dengan stok minim atau habis. Segera lakukan pembelian!</div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon"><i class="fas fa-boxes"></i></div>
        <div>
            <h3>Data Produk / Barang Textile</h3>
            <p>Kelola stok kain dan bahan textile</p>
        </div>
        <a href="tambah_barang.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Produk
        </a>
    </div>

    <!-- TOOLBAR -->
    <div class="page-toolbar">
        <form method="GET" style="display:flex;gap:10px;flex:1;flex-wrap:wrap;align-items:center">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari produk..." value="<?= htmlspecialchars($search) ?>">
            </div>
            <select name="kategori" class="form-control no-icon" style="width:180px;padding:9px 12px" onchange="this.form.submit()">
                <option value="">Semua Kategori</option>
                <?php $kategori_list->data_seek(0); while ($kl = $kategori_list->fetch_assoc()): ?>
                <option value="<?= $kl['id'] ?>" <?= $filter_kat == $kl['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($kl['nama_kategori']) ?>
                </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="kelola_barang.php" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Reset</a>
        </form>
        <span style="font-size:13px;color:var(--text-muted)"><?= $data->num_rows ?> produk</span>
    </div>

    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th>No</th>
                    <th>Kode</th>
                    <th>Produk</th>
                    <th>Kategori</th>
                    <th>Harga Beli</th>
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Status</th>
                    <th width="130">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($data->num_rows === 0): ?>
            <tr><td colspan="9">
                <div class="empty-state">
                    <div class="es-icon">📦</div>
                    <h4>Tidak ada produk</h4>
                    <p>Coba ubah filter atau <a href="tambah_barang.php">tambah produk baru</a></p>
                </div>
            </td></tr>
            <?php else: ?>
            <?php $no = 1; while ($row = $data->fetch_assoc()):
                $stok_pct = $row['stok_minimum'] > 0 ? min(100, round($row['stok'] / $row['stok_minimum'] * 100)) : 100;
                $stok_class = $row['stok'] == 0 ? 'stok-empty' : ($row['stok'] <= $row['stok_minimum'] ? 'stok-low' : 'stok-ok');
            ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><code style="font-size:12px;background:#f8fafc;padding:3px 6px;border-radius:4px"><?= $row['kode_barang'] ?></code></td>
                <td>
                    <div style="font-weight:600"><?= htmlspecialchars($row['nama_barang']) ?></div>
                    <div style="font-size:11px;color:var(--text-muted)"><?= htmlspecialchars($row['bahan'] ?? '') ?> <?= $row['warna'] ? '· ' . $row['warna'] : '' ?></div>
                </td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($row['nama_kategori']) ?></span></td>
                <td><?= rupiah($row['harga_beli']) ?></td>
                <td style="font-weight:600;color:var(--success)"><?= rupiah($row['harga_jual']) ?></td>
                <td>
                    <div style="font-weight:700"><?= $row['stok'] ?> <small style="font-weight:400;color:var(--text-muted)"><?= $row['satuan'] ?></small></div>
                    <div class="stok-bar <?= $stok_class ?>">
                        <div class="stok-bar-fill" style="width:<?= $stok_pct ?>%"></div>
                    </div>
                    <div style="font-size:10px;color:var(--text-muted)">Min: <?= $row['stok_minimum'] ?></div>
                </td>
                <td>
                    <?php if ($row['stok'] == 0): ?>
                        <span class="badge badge-danger">Habis</span>
                    <?php elseif ($row['stok'] <= $row['stok_minimum']): ?>
                        <span class="badge badge-warning">Stok Minim</span>
                    <?php else: ?>
                        <span class="badge badge-success">Normal</span>
                    <?php endif; ?>
                </td>
                <td>
                    <div class="action-btns">
                        <a href="edit_barang.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" data-tooltip="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="confirmDelete('../barang/hapus_barang.php?id=<?= $row['id'] ?>','<?= htmlspecialchars($row['nama_barang']) ?>')" class="btn btn-danger btn-sm" data-tooltip="Hapus">
                            <i class="fas fa-trash"></i>
                        </a>
                    </div>
                </td>
            </tr>
            <?php endwhile; ?>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include '../footer.php';