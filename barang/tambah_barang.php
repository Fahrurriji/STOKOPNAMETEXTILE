<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Tambah Produk';

$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori");
$pemasok_list  = $conn->query("SELECT * FROM pemasok ORDER BY nama_pemasok");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode       = strtoupper(trim(esc($conn, $_POST['kode_barang'])));
    $nama       = esc($conn, trim($_POST['nama_barang']));
    $id_kat     = (int)$_POST['id_kategori'];
    $id_pms     = (int)$_POST['id_pemasok'];
    $satuan     = esc($conn, $_POST['satuan']);
    $harga_beli = (float)str_replace([',', '.'], ['', '.'], $_POST['harga_beli']);
    $harga_jual = (float)str_replace([',', '.'], ['', '.'], $_POST['harga_jual']);
    $stok       = (int)$_POST['stok'];
    $stok_min   = (int)$_POST['stok_minimum'];
    $lokasi     = esc($conn, $_POST['lokasi_gudang']);
    $warna      = esc($conn, $_POST['warna']);
    $motif      = esc($conn, $_POST['motif']);
    $bahan      = esc($conn, $_POST['bahan']);
    $lebar      = esc($conn, $_POST['lebar_kain']);
    $keterangan = esc($conn, $_POST['keterangan']);

    if (empty($kode)) $kode = generate_kode($conn, 'barang', 'kode_barang', 'BRG');

    // Upload gambar
    $gambar = 'no-image.png';
    if (!empty($_FILES['gambar']['name'])) {
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg','jpeg','png','webp'];
        if (!in_array($ext, $allowed)) {
            $error = 'Format gambar harus JPG, JPEG, PNG, atau WEBP!';
        } else {
            $gambar = 'barang_' . time() . '.' . $ext;
            move_uploaded_file($_FILES['gambar']['tmp_name'], '../gambar/' . $gambar);
        }
    }

    if (!isset($error)) {
        $cek = $conn->query("SELECT id FROM barang WHERE kode_barang='$kode'");
        if ($cek->num_rows > 0) {
            $error = 'Kode barang sudah ada!';
        } else {
            $sql = "INSERT INTO barang (kode_barang,nama_barang,id_kategori,id_pemasok,satuan,harga_beli,harga_jual,stok,stok_minimum,lokasi_gudang,warna,motif,bahan,lebar_kain,gambar,keterangan)
                    VALUES ('$kode','$nama',$id_kat," . ($id_pms ?: 'NULL') . ",'$satuan',$harga_beli,$harga_jual,$stok,$stok_min,'$lokasi','$warna','$motif','$bahan','$lebar','$gambar','$keterangan')";
            if ($conn->query($sql)) {
                $_SESSION['pesan'] = "Produk <strong>$nama</strong> berhasil ditambahkan!";
                header('Location: kelola_barang.php'); exit;
            } else {
                $error = 'Gagal menyimpan: ' . $conn->error;
            }
        }
    }
}

$kode_otomatis = generate_kode($conn, 'barang', 'kode_barang', 'BRG');
include '../config/header.php';
?>

<?php if (isset($error)): ?>
<div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <div class="card-header-icon"><i class="fas fa-box-open"></i></div>
        <div>
            <h3>Tambah Produk / Barang Baru</h3>
            <p>Lengkapi informasi produk textile</p>
        </div>
    </div>
    <div class="card-body">
        <form method="POST" enctype="multipart/form-data">
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px">
                <!-- Kolom Kiri -->
                <div>
                    <h4 style="font-size:14px;font-weight:700;color:var(--primary-dark);margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--accent)">
                        <i class="fas fa-info-circle" style="color:var(--accent)"></i> Informasi Dasar
                    </h4>
                    <div class="form-group">
                        <label>Kode Barang</label>
                        <div class="input-wrap">
                            <i class="fas fa-barcode input-icon"></i>
                            <input type="text" name="kode_barang" class="form-control" placeholder="<?= $kode_otomatis ?>" style="text-transform:uppercase">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Nama Produk <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-box input-icon"></i>
                            <input type="text" name="nama_barang" class="form-control" required placeholder="Nama kain/produk...">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Kategori <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-tags input-icon"></i>
                            <select name="id_kategori" class="form-control" required>
                                <option value="">-- Pilih Kategori --</option>
                                <?php while ($kl = $kategori_list->fetch_assoc()): ?>
                                <option value="<?= $kl['id'] ?>"><?= htmlspecialchars($kl['nama_kategori']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Pemasok</label>
                        <div class="input-wrap">
                            <i class="fas fa-truck input-icon"></i>
                            <select name="id_pemasok" class="form-control">
                                <option value="">-- Pilih Pemasok --</option>
                                <?php while ($pl = $pemasok_list->fetch_assoc()): ?>
                                <option value="<?= $pl['id'] ?>"><?= htmlspecialchars($pl['nama_pemasok']) ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Satuan <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-ruler input-icon"></i>
                            <select name="satuan" class="form-control" required>
                                <option value="meter">Meter</option>
                                <option value="yard">Yard</option>
                                <option value="kg">Kilogram</option>
                                <option value="roll">Roll</option>
                                <option value="pcs">Pcs</option>
                                <option value="lusin">Lusin</option>
                                <option value="gulung">Gulung</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div>
                    <h4 style="font-size:14px;font-weight:700;color:var(--primary-dark);margin-bottom:16px;padding-bottom:8px;border-bottom:2px solid var(--accent)">
                        <i class="fas fa-tags" style="color:var(--accent)"></i> Harga & Stok
                    </h4>
                    <div class="form-group">
                        <label>Harga Beli (Modal) <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-money-bill input-icon"></i>
                            <input type="number" name="harga_beli" class="form-control" required min="0" step="100" placeholder="0">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Harga Jual <span>*</span></label>
                        <div class="input-wrap">
                            <i class="fas fa-tag input-icon"></i>
                            <input type="number" name="harga_jual" class="form-control" required min="0" step="100" placeholder="0">
                        </div>
                    </div>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Stok Awal <span>*</span></label>
                            <div class="input-wrap">
                                <i class="fas fa-layer-group input-icon"></i>
                                <input type="number" name="stok" class="form-control" required min="0" value="0">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Stok Minimum</label>
                            <div class="input-wrap">
                                <i class="fas fa-exclamation input-icon"></i>
                                <input type="number" name="stok_minimum" class="form-control" min="0" value="10">
                            </div>
                        </div>
                    </div>

                    <h4 style="font-size:14px;font-weight:700;color:var(--primary-dark);margin:20px 0 16px;padding-bottom:8px;border-bottom:2px solid var(--accent)">
                        <i class="fas fa-tshirt" style="color:var(--accent)"></i> Detail Textile
                    </h4>
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
                        <div class="form-group">
                            <label>Warna</label>
                            <div class="input-wrap">
                                <i class="fas fa-palette input-icon"></i>
                                <input type="text" name="warna" class="form-control" placeholder="Merah, Biru...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Motif</label>
                            <div class="input-wrap">
                                <i class="fas fa-drafting-compass input-icon"></i>
                                <input type="text" name="motif" class="form-control" placeholder="Polos, Batik...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Bahan</label>
                            <div class="input-wrap">
                                <i class="fas fa-leaf input-icon"></i>
                                <input type="text" name="bahan" class="form-control" placeholder="100% Katun...">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Lebar Kain</label>
                            <div class="input-wrap">
                                <i class="fas fa-arrows-alt-h input-icon"></i>
                                <input type="text" name="lebar_kain" class="form-control" placeholder="150 cm">
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Lokasi Gudang</label>
                        <div class="input-wrap">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" name="lokasi_gudang" class="form-control" placeholder="Rak A-1, Laci 3...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bawah: Gambar & Keterangan -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-top:8px">
                <div class="form-group">
                    <label>Gambar Produk</label>
                    <input type="file" name="gambar" class="form-control no-icon" accept="image/*">
                    <small style="color:var(--text-muted)">JPG, PNG, WEBP (max 2MB)</small>
                </div>
                <div class="form-group">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control no-icon" rows="3" placeholder="Catatan tambahan..."></textarea>
                </div>
            </div>

            <div style="display:flex;gap:10px;margin-top:12px;padding-top:20px;border-top:1px solid var(--border)">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Simpan Produk
                </button>
                <a href="kelola_barang.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Batal
                </a>
            </div>
        </form>
    </div>
</div>

<?php include '../config/footer.php'; ?>