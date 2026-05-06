<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Edit Kategori';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: kelola_kategori.php'); exit; }

$kat = $conn->query("SELECT * FROM kategori WHERE id=$id")->fetch_assoc();
if (!$kat) { header('Location: kelola_kategori.php'); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $kode  = strtoupper(trim(esc($conn, $_POST['kode_kategori'])));
    $nama  = esc($conn, trim($_POST['nama_kategori']));
    $deskripsi = esc($conn, trim($_POST['deskripsi']));

    // Cek duplikat kode (selain id ini)
    $cek = $conn->query("SELECT id FROM kategori WHERE kode_kategori='$kode' AND id != $id");
    if ($cek->num_rows > 0) {
        $error = 'Kode kategori sudah digunakan!';
    } else {
        $sql = "UPDATE kategori SET kode_kategori='$kode', nama_kategori='$nama', deskripsi='$deskripsi' WHERE id=$id";
        if ($conn->query($sql)) {
            $_SESSION['pesan'] = "Kategori berhasil diperbarui!";
            header('Location: kelola_kategori.php'); exit;
        } else {
            $error = 'Gagal update: ' . $conn->error;
        }
    }
}

include '../config/header.php';
?>

<div style="max-width:620px;margin:0 auto">
    <?php if (isset($error)): ?>
    <div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <div class="card-header-icon"><i class="fas fa-edit"></i></div>
            <div>
                <h3>Edit Kategori</h3>
                <p>Perbarui data kategori #<?= $kat['kode_kategori'] ?></p>
            </div>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Kode Kategori <span>*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-barcode input-icon"></i>
                        <input type="text" name="kode_kategori" class="form-control"
                               value="<?= htmlspecialchars($kat['kode_kategori']) ?>"
                               required style="text-transform:uppercase">
                    </div>
                </div>
                <div class="form-group">
                    <label>Nama Kategori <span>*</span></label>
                    <div class="input-wrap">
                        <i class="fas fa-tag input-icon"></i>
                        <input type="text" name="nama_kategori" class="form-control"
                               value="<?= htmlspecialchars($kat['nama_kategori']) ?>" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="deskripsi" class="form-control no-icon" rows="3"><?= htmlspecialchars($kat['deskripsi'] ?? '') ?></textarea>
                </div>
                <div style="display:flex;gap:10px;margin-top:8px">
                    <button type="submit" class="btn btn-warning">
                        <i class="fas fa-save"></i> Update Kategori
                    </button>
                    <a href="kelola_kategori.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include '../config/footer.php'; ?>