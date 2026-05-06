<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Edit Produk';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: kelola_barang.php'); exit; }

$brg = $conn->query("SELECT * FROM barang WHERE id=$id")->fetch_assoc();
if (!$brg) { header('Location: kelola_barang.php'); exit; }

$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
$pemasok_list  = $conn->query("SELECT * FROM pemasok ORDER BY nama_pemasok");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode       = strtoupper(trim(esc($conn, $_POST['kode_barang'])));
    $nama       = esc($conn, trim($_POST['nama_barang']));
    $id_kat     = (int)$_POST['id_kategori'];
    $id_pms     = (int)$_POST['id_pemasok'];
    $satuan     = esc($conn, $_POST['satuan']);
    $harga_beli = (float)$_POST['harga_beli'];
    $harga_jual = (float)$_POST['harga_jual'];
    $stok_min   = (int)$_POST['stok_minimum'];
    $lokasi     = esc($conn, $_POST['lokasi_gudang']);
    $warna      = esc($conn, $_POST['warna']);
    $motif      = esc($conn, $_POST['motif']);
    $bahan      = esc($conn, $_POST['bahan']);
    $lebar      = esc($conn, $_POST['lebar_kain']);
    $keterangan = esc($conn, $_POST['keterangan']);
    $status     = esc($conn, $_POST['status']);

    // Upload gambar baru
    $gambar = $brg['gambar'];
    if (!empty($_FILES['gambar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP!';
        } else {
            $gambar_baru = 'barang_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], '../gambar/' . $gambar_baru)) {
                // Hapus gambar lama
                if ($brg['gambar'] && $brg['gambar'] !== 'no-image.png') {
                    @unlink('../gambar/' . $brg['gambar']);
                }
                $gambar = $gambar_baru;
            }
        }
    }

    if (!isset($error)) {
        $cek = $conn->query("SELECT id FROM barang WHERE kode_barang='$kode' AND id != $id");
        if ($cek->num_rows > 0) {
            $error = 'Kode barang sudah digunakan produk lain!';
        } else {
            $pms_val = $id_pms ? $id_pms : 'NULL';
            $sql = "UPDATE barang SET kode_barang='$kode', nama_barang='$nama', id_kategori=$id_kat, id_pemasok=$pms_val,
                    satuan='$satuan', harga_beli=$harga_beli, harga_jual=$harga_jual, stok_minimum=$stok_min,
                    lokasi_gudang='$lokasi', warna='$warna', motif='$motif', bahan='$bahan', lebar_kain='$lebar',
                    gambar='$gambar', keterangan='$keterangan', status='$status' WHERE id=$id";
            if ($conn->query($sql)) {
                $_SESSION['pesan'] = "Produk berhasil diperbarui!";
                header('Location: kelola_barang.php'); exit;
            } else {
                $error = 'Gagal update: ' . $conn->error;
            }
        }
    }
}

include '../config/header.php';
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon"><i class="fas fa-edit"></i></div>
        <div>
            <h3>Edit Produk</h3>
            <p>Perbarui data produk: <?= htmlspecialchars($brg['nama_barang']) ?></p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <div>
                    <div class="form-group">
                        <label>Kode Barang <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-barcode input-icon"></i>
                            <input type="text" name="kode_barang" class="form-control" value="<?= htmlspecialchars($brg['kode_barang']) ?>" required style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nama Produk <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-box input-icon"></i>
                            <input type="text" name="nama_barang" class="form-control" value="<?= htmlspecialchars($brg['nama_barang']) ?>" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-tags input-icon"></i>
                            <select name="id_kategori" class="form-control" required>
                                <?php $kategori_list->data_seek(0); while ($kl = $kategori_list->fetch_assoc()): ?>
                                <option value="<?= $kl['id'] ?>" <?= $brg['id_kategori'] == $kl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($kl['nama_kategori']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pemasok</label>
                        <div class="input-wrap">
                            <i class="fas fa-truck input-icon"></i>
                            <select name="id_pemasok" class="form-control">
                                <option value="">-- Tidak Ada --</option>
                                <?php $pemasok_list->data_seek(0); while ($pl = $pemasok_list->fetch_assoc()): ?>
                                <option value="<?= $pl['id'] ?>" <?= $brg['id_pemasok'] == $pl['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($pl['nama_pemasok']) ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Satuan</label>
                        <div class="input-wrap">
                            <i class="fas fa-ruler input-icon"></i>
                            <select name="satuan" class="form-control">
                                <?php foreach (['meter','yard','kg','roll','pcs','lusin','gulung'] as $s): ?>
                                <option value="<?= $s ?>" <?= $brg['satuan'] == $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status</label>
                        <div class="input-wrap">
                            <i class="fas fa-toggle-on input-icon"></i>
                            <select name="status" class="form-control">
                                <option value="aktif" <?= $brg['status'] == 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= $brg['status'] == 'nonaktif' ? 'selected' : '' ?>>Non-aktif</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <div class="form-group">
                        <label>Harga Beli <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-money-bill input-icon"></i>
                            <input type="number" name="harga_beli" class="form-control" value="<?= $brg['harga_beli'] ?>" required min="0" step="100">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="number" name="harga_jual" class="form-control" value="<?= $brg['harga_jual'] ?>" required min="0" step="100">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Stok Saat Ini</label>
                        <div style="padding:10px 14px;background:#f8fafc;border:1.5px solid var(--border);border-radius:8px;font-weight:700;font-size:18px;color:var(--primary-dark)">
                            <?= $brg['stok'] ?> <span style="font-size:13px;font-weight:400;color:var(--text-muted)"><?= $brg['satuan'] ?></span>
                        </div>
                        <small style="color:var(--text-muted)">Ubah stok melalui transaksi masuk/keluar</small>
                    </div>
                    <div class="form-group">
                        <label>Stok Minimum</label>
                        <div class="input-wrap">
                            <i class="fas fa-exclamation input-icon"></i>
                            <input type="number" name="stok_minimum" class="form-control" value="<?= $brg['stok_minimum'] ?>" min="0">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Warna</label>
                            <div class="input-wrap">
                                <i class="fas fa-palette input-icon"></i>
                                <input type="text" name="warna" class="form-control" value="<?= htmlspecialchars($brg['warna'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Motif</label>
                            <div class="input-wrap">
                                <i class="fas fa-drafting-compass input-icon"></i>
                                <input type="text" name="motif" class="form-control" value="<?= htmlspecialchars($brg['motif'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Bahan</label>
                            <div class="input-wrap">
                                <i class="fas fa-leaf input-icon"></i>
                                <input type="text" name="bahan" class="form-control" value="<?= htmlspecialchars($brg['bahan'] ?? '') ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Lebar Kain</label>
                            <div class="input-wrap">
                                <i class="fas fa-arrows-alt-h input-icon"></i>
                                <input type="text" name="lebar_kain" class="form-control" value="<?= htmlspecialchars($brg['lebar_kain'] ?? '') ?>">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Lokasi Gudang</label>
                        <div class="input-wrap">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" name="lokasi_gudang" class="form-control" value="<?= htmlspecialchars($brg['lokasi_gudang'] ?? '') ?>">
                        </div>
                    </div>
                </div>
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:8px">
                <div class="form-group">
                    <label>Gambar (kosongkan jika tidak diganti)</label>
                    <?php if ($brg['gambar'] && $brg['gambar'] !== 'no-image.png'): ?>
                    <div style="margin-bottom:8px">
                        <img src="../gambar/<?= htmlspecialchars($brg['gambar']) ?>" style="height:80px;border-radius:8px;object-fit:cover;border:2px solid var(--border)" onerror="this.style.display='none'">
                    </div>
                    <?php endif; ?>
                    <input type="file" name="gambar" class="form-control no-icon" accept="image/*">
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control no-icon" rows="4"><?= htmlspecialchars($brg['keterangan'] ?? '') ?></textarea>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:12px;padding-top:20px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-warning">
                    <i class="fas fa-save"></i> Update Produk
                </button>
                <a href="kelola_barang.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../config/footer.php'; ?>