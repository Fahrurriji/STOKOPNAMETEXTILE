<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Stock Opname';
if (isset($_SESSION['pesan'])) { $pesan = $_SESSION['pesan']; unset($_SESSION['pesan']); }

// Proses simpan opname
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tanggal = esc($conn, $_POST['tanggal']);
    $keterangan = esc($conn, $_POST['keterangan']);
    $stok_fisik = $_POST['stok_fisik'] ?? [];
    $id_barang  = $_POST['id_barang'] ?? [];

    $no_opname = generate_kode($conn, 'stock_opname', 'no_opname', 'SO-' . date('Ym') . '-');
    $conn->begin_transaction();
    try {
        $conn->query("INSERT INTO stock_opname (no_opname, id_admin, tanggal, keterangan, status)
            VALUES ('$no_opname', {$_SESSION['user_id']}, '$tanggal', '$keterangan', 'selesai')");
        $id_so = $conn->insert_id;

        foreach ($id_barang as $key => $id_brg) {
            $id_brg   = (int)$id_brg;
            $fisik    = (int)($stok_fisik[$key] ?? 0);
            $sistem   = (int)$conn->query("SELECT stok FROM barang WHERE id=$id_brg")->fetch_assoc()['stok'];
            $selisih  = $fisik - $sistem;
            $conn->query("INSERT INTO detail_stock_opname (id_opname, id_barang, stok_sistem, stok_fisik, selisih)
                VALUES ($id_so, $id_brg, $sistem, $fisik, $selisih)");
            // Update stok sistem ke stok fisik
            $conn->query("UPDATE barang SET stok = $fisik WHERE id = $id_brg");
        }
        $conn->commit();
        $_SESSION['pesan'] = "Stock Opname <strong>$no_opname</strong> berhasil disimpan!";
        header('Location: stock_opname.php'); exit;
    } catch (Exception $e) {
        $conn->rollback();
        $error = "Gagal: " . $e->getMessage();
    }
}

// History opname
$history = $conn->query("SELECT so.*, a.nama_lengkap FROM stock_opname so LEFT JOIN admin a ON so.id_admin=a.id ORDER BY so.id DESC LIMIT 10");

// Data barang untuk form
$barang_all = $conn->query("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.status='aktif' ORDER BY k.nama_kategori, b.nama_barang");

include 'header.php';
?>
<?php if (isset($pesan)): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= $pesan ?></div><?php endif; ?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>

<div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">
  <!-- Form Opname -->
  <div class="card">
    <div class="card-header">
      <div class="card-header-icon"><i class="fas fa-clipboard-check"></i></div>
      <div><h3>Form Stock Opname</h3><p>Masukkan jumlah stok fisik aktual di gudang</p></div>
    </div>
    <div class="card-body">
      <form method="POST">
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;padding-bottom:16px;border-bottom:2px dashed var(--border)">
          <div class="form-group">
            <label>Tanggal Opname <span style="color:red">*</span></label>
            <div class="input-wrap"><i class="fas fa-calendar input-icon"></i>
              <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
            </div>
          </div>
          <div class="form-group">
            <label>Keterangan</label>
            <div class="input-wrap"><i class="fas fa-note-sticky input-icon"></i>
              <input type="text" name="keterangan" class="form-control" placeholder="Opname bulanan...">
            </div>
          </div>
        </div>

        <!-- Cari produk -->
        <div class="search-box" style="margin-bottom:16px;max-width:100%">
          <i class="fas fa-search"></i>
          <input type="text" id="cariProduk" placeholder="Cari produk untuk filter tabel..." oninput="filterTable(this.value)">
        </div>

        <div class="table-wrap" style="max-height:500px;overflow-y:auto">
          <table class="table" id="tblOpname">
            <thead style="position:sticky;top:0;z-index:10">
              <tr>
                <th>Produk</th>
                <th>Kategori</th>
                <th>Stok Sistem</th>
                <th>Stok Fisik</th>
                <th>Selisih</th>
              </tr>
            </thead>
            <tbody>
            <?php $kat_prev = ''; while ($b = $barang_all->fetch_assoc()):
              $nama_kat = $b['nama_kategori'];
            ?>
            <?php if ($nama_kat !== $kat_prev): $kat_prev = $nama_kat; ?>
            <tr style="background:linear-gradient(90deg,var(--primary-dark),var(--primary));color:#fff" data-group>
              <td colspan="5" style="padding:10px 16px;font-weight:700;font-size:12px;letter-spacing:1px;text-transform:uppercase">
                <i class="fas fa-folder-open"></i> <?= htmlspecialchars($nama_kat) ?>
              </td>
            </tr>
            <?php endif; ?>
            <tr data-nama="<?= strtolower(htmlspecialchars($b['nama_barang'])) ?>">
              <td>
                <input type="hidden" name="id_barang[]" value="<?= $b['id'] ?>">
                <strong><?= htmlspecialchars($b['nama_barang']) ?></strong>
                <div style="font-size:11px;color:var(--text-muted)"><?= $b['kode_barang'] ?> · <?= $b['satuan'] ?></div>
              </td>
              <td><span class="badge badge-primary"><?= htmlspecialchars($b['nama_kategori']) ?></span></td>
              <td>
                <strong><?= $b['stok'] ?></strong> <span style="color:var(--text-muted);font-size:12px"><?= $b['satuan'] ?></span>
              </td>
              <td>
                <input type="number" name="stok_fisik[]" class="form-control no-icon fisik-input"
                       style="padding:8px 10px;width:100px"
                       value="<?= $b['stok'] ?>" min="0"
                       data-sistem="<?= $b['stok'] ?>"
                       onchange="updateSelisih(this)">
              </td>
              <td id="sel_<?= $b['id'] ?>" style="font-weight:700">
                <span style="color:var(--text-muted)">0</span>
              </td>
            </tr>
            <?php endwhile; ?>
            </tbody>
          </table>
        </div>

        <div style="display:flex;gap:10px;margin-top:20px;padding-top:16px;border-top:1px solid var(--border)">
          <button type="submit" class="btn btn-accent"><i class="fas fa-clipboard-check"></i> Simpan Stock Opname</button>
          <a href="stock_opname.php" class="btn btn-secondary"><i class="fas fa-redo"></i> Reset</a>
        </div>
      </form>
    </div>
  </div>

  <!-- Riwayat Opname -->
  <div class="card" style="height:fit-content">
    <div class="card-header">
      <div class="card-header-icon"><i class="fas fa-history"></i></div>
      <div><h3>Riwayat Opname</h3><p>10 opname terakhir</p></div>
    </div>
    <div class="table-wrap">
      <table class="table">
        <thead><tr><th>No. Opname</th><th>Tanggal</th><th>Admin</th></tr></thead>
        <tbody>
        <?php if ($history->num_rows === 0): ?>
          <tr><td colspan="3" class="text-center" style="padding:24px;color:var(--text-muted)">Belum ada riwayat</td></tr>
        <?php else: while ($h = $history->fetch_assoc()): ?>
          <tr>
            <td><span class="badge badge-gold"><?= $h['no_opname'] ?></span></td>
            <td style="font-size:12px"><?= tgl_indo($h['tanggal']) ?></td>
            <td style="font-size:12px"><?= htmlspecialchars($h['nama_lengkap']) ?></td>
          </tr>
        <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<script>
function updateSelisih(input) {
    const fisik   = parseInt(input.value) || 0;
    const sistem  = parseInt(input.dataset.sistem) || 0;
    const selisih = fisik - sistem;
    const row     = input.closest('tr');
    const idBarang = row.querySelector('input[name="id_barang[]"]')?.value;
    const selEl   = document.getElementById('sel_' + idBarang);
    if (selEl) {
        if (selisih > 0) selEl.innerHTML = `<span style="color:var(--success)">+${selisih}</span>`;
        else if (selisih < 0) selEl.innerHTML = `<span style="color:var(--danger)">${selisih}</span>`;
        else selEl.innerHTML = `<span style="color:var(--text-muted)">0</span>`;
    }
}

function filterTable(val) {
    val = val.toLowerCase();
    document.querySelectorAll('#tblOpname tbody tr:not([data-group])').forEach(tr => {
        tr.style.display = (!val || tr.dataset.nama?.includes(val)) ? '' : 'none';
    });
}
</script>
<?php include 'footer.php'; ?>