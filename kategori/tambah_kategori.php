<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Tambah Kategori';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode  = strtoupper(trim(esc($conn, $_POST['kode_kategori'])));
    $nama  = esc($conn, trim($_POST['nama_kategori']));
    $deskripsi = esc($conn, trim($_POST['deskripsi']));

    // Auto generate kode jika kosong
    if (empty($kode)) {
        $kode = generate_kode($conn, 'kategori', 'kode_kategori', 'KAT');
    }

    // Cek duplikat
    $cek = $conn->query("SELECT id FROM kategori WHERE kode_kategori='$kode'");
    if ($cek->num_rows > 0) {
        $error = 'Kode kategori sudah ada!';
    } else {
        $sql = "INSERT INTO kategori (kode_kategori, nama_kategori, deskripsi) VALUES ('$kode','$nama','$deskripsi')";
        if ($conn->query($sql)) {
            $_SESSION['pesan'] = "Kategori <strong>$nama</strong> berhasil ditambahkan!";
            header('Location: kelola_kategori.php'); exit;
        } else {
            $error = 'Gagal menyimpan: ' . $conn->error;
        }
    }
}

// Auto-generate kode untuk tampilan
$kode_otomatis = generate_kode($conn, 'kategori', 'kode_kategori', 'KAT');

include '../header.php';
?>

<div style="max-width:620px;margin:0 auto">
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-tag"></i></div>
            <div>
                <h3>Tambah Kategori Baru</h3>
                <p>Isi data kategori produk textile</p>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Kode Kategori <span style="color:var(--text-muted);font-weight:400;font-size:12px">(bisa dikosongkan, otomatis)</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-barcode input-icon"></i>
                        <input type="text" name="kode_kategori" class="form-control"
                               placeholder="<?= $kode_otomatis ?>"
                               value="<?= htmlspecialchars($_POST['kode_kategori'] ?? '') ?>"
                               style="text-transform:uppercase">
                    </div>
                </div>
                <div class="form-group">
                    <label>Nama Kategori <span>*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-tag input-icon"></i>
                        <input type="text" name="nama_kategori" class="form-control"
                               placeholder="Contoh: Kain Katun, Kain Sutra..."
                               value="<?= htmlspecialchars($_POST['nama_kategori'] ?? '') ?>"
                               required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control no-icon" rows="3"
                              placeholder="Keterangan kategori..."><?= htmlspecialchars($_POST['deskripsi'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;gap:10px;margin-top:8px">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Simpan Kategori
                    </button>
                    <a href="kelola_kategori.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../footer.php';