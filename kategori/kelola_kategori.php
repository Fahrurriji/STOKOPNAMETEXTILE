<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
cek_admin();
$page_title = 'Kelola Kategori';

$pesan = '';
$error = '';

// Ambil data kategori
$search = isset($_GET['search']) ? esc($conn, $_GET['search']) : '';
$sql = "SELECT k.*, COUNT(b.id) as jml_barang FROM kategori k LEFT JOIN barang b ON k.id = b.id_kategori";
if ($search) $sql .= " WHERE k.nama_kategori LIKE '%$search%' OR k.kode_kategori LIKE '%$search%'";
$sql .= " GROUP BY k.id ORDER BY k.id DESC";
$data = $conn->query($sql);

if (isset($_SESSION['pesan'])) { $pesan = $_SESSION['pesan']; unset($_SESSION['pesan']); }
if (isset($_SESSION['error'])) { $error = $_SESSION['error']; unset($_SESSION['error']); }

include '../header.php';
?>

<?php if ($pesan): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div><?php endif; ?>
<?php if ($error): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon"><i class="fas fa-tags"></i></div>
        <div>
            <h3>Data Kategori Produk</h3>
            <p>Kelola kategori jenis kain dan bahan textile</p>
        </div>
        <a href="tambah_kategori.php" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> Tambah Kategori
        </a>
    </div>

    <!-- TOOLBAR -->
    <div class="page-toolbar">
        <form method="GET" style="display:flex;gap:10px;flex:1">
            <div class="search-box" style="max-width:320px">
                <i class="fas fa-search"></i>
                <input type="text" name="search" placeholder="Cari kategori..."
                       value="<?= htmlspecialchars($search) ?>"
                       onchange="this.form.submit()">
            </div>
        </form>
        <span style="font-size:13px;color:var(--text-muted)"><?= $data->num_rows ?> kategori ditemukan</span>
    </div>

    <div class="table-wrap">
        <table class="table" id="tblKategori">
            <thead>
                <tr>
                    <th width="50">No</th>
                    <th>Kode</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jml Produk</th>
                    <th>Dibuat</th>
                    <th width="160">Aksi</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($data->num_rows === 0): ?>
            <tr>
                <td colspan="7">
                    <div class="empty-state">
                        <div class="es-icon">🏷️</div>
                        <h4>Belum ada kategori</h4>
                        <p>Klik tombol "Tambah Kategori" untuk menambah data baru</p>
                    </div>
                </td>
            </tr>
            <?php else: ?>
            <?php $no = 1; while ($row = $data->fetch_assoc()): ?>
            <tr>
                <td><?= $no++ ?></td>
                <td><span class="badge badge-primary"><?= htmlspecialchars($row['kode_kategori']) ?></span></td>
                <td><strong><?= htmlspecialchars($row['nama_kategori']) ?></strong></td>
                <td style="color:var(--text-muted)"><?= htmlspecialchars($row['deskripsi'] ?? '-') ?></td>
                <td>
                    <span class="badge badge-gold"><?= $row['jml_barang'] ?> produk</span>
                </td>
                <td style="font-size:12px;color:var(--text-muted)"><?= tgl_indo(date('Y-m-d', strtotime($row['created_at']))) ?></td>
                <td>
                    <div class="action-btns">
                        <a href="edit_kategori.php?id=<?= $row['id'] ?>" class="btn btn-warning btn-sm" data-tooltip="Edit">
                            <i class="fas fa-edit"></i>
                        </a>
                        <a href="#" onclick="confirmDelete('hapus_kategori.php?id=<?= $row['id'] ?>','<?= htmlspecialchars($row['nama_kategori']) ?>')" class="btn btn-danger btn-sm" data-tooltip="Hapus">
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

<?php include '../footer.php'; ?>
