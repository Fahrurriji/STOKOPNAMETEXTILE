<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Tambah Barang Masuk';

$barang_list  = $conn->query("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.status='aktif' ORDER BY b.nama_barang");
$pemasok_list = $conn->query("SELECT * FROM pemasok ORDER BY nama_pemasok");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pemasok = (int)$_POST['id_pemasok'];
    $tanggal    = esc($conn, $_POST['tanggal']);
    $keterangan = esc($conn, $_POST['keterangan']);
    $items      = $_POST['items'] ?? [];

    if (empty($items)) {
        $error = 'Minimal tambahkan 1 produk!';
    } else {
        $no_trans = generate_kode($conn, 'transaksi_masuk', 'no_transaksi', 'TM-' . date('Ym') . '-');
        $pms_val  = $id_pemasok ?: 'NULL';
        $total_item = 0;
        $total_nilai = 0;

        foreach ($items as $item) {
            $total_item++;
            $total_nilai += $item['jumlah'] * $item['harga'];
        }

        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO transaksi_masuk (no_transaksi, id_pemasok, id_admin, tanggal, total_item, total_nilai, keterangan)
                VALUES ('$no_trans', $pms_val, {$_SESSION['user_id']}, '$tanggal', $total_item, $total_nilai, '$keterangan')");
            $id_trans = $conn->insert_id;

            foreach ($items as $item) {
                $id_brg  = (int)$item['id_barang'];
                $jumlah  = (int)$item['jumlah'];
                $harga   = (float)$item['harga'];
                $subtotal = $jumlah * $harga;
                $conn->query("INSERT INTO detail_transaksi_masuk (id_transaksi, id_barang, jumlah, harga_beli, subtotal)
                    VALUES ($id_trans, $id_brg, $jumlah, $harga, $subtotal)");
                // Update stok
                $conn->query("UPDATE barang SET stok = stok + $jumlah WHERE id = $id_brg");
            }

            $conn->commit();
            $_SESSION['pesan'] = "Transaksi <strong>$no_trans</strong> berhasil disimpan!";
            header('Location: transaksi_masuk.php'); exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal menyimpan: ' . $e->getMessage();
        }
    }
}

include 'header.php';
?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>

<div class="card">
  <div class="card-header">
    <div class="card-header-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-arrow-down"></i></div>
    <div><h3>Tambah Transaksi Barang Masuk</h3><p>Catat pembelian kain dari pemasok</p></div>
  </div>
  <div class="card-body">
    <form method="POST" id="frmMasuk">
      <!-- Header Transaksi -->
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:20px;margin-bottom:24px;padding-bottom:20px;border-bottom:2px dashed var(--border)">
        <div class="form-group">
          <label>Pemasok</label>
          <div class="input-wrap"><i class="fas fa-truck input-icon"></i>
            <select name="id_pemasok" class="form-control">
              <option value="">-- Pilih Pemasok --</option>
              <?php while ($p = $pemasok_list->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_pemasok']) ?></option>
              <?php endwhile; ?>
            </select>
          </div>
        </div>
        <div class="form-group">
          <label>Tanggal <span style="color:red">*</span></label>
          <div class="input-wrap"><i class="fas fa-calendar input-icon"></i>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
          </div>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <div class="input-wrap"><i class="fas fa-note-sticky input-icon"></i>
            <input type="text" name="keterangan" class="form-control" placeholder="Catatan...">
          </div>
        </div>
      </div>

      <!-- Tambah Item -->
      <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
        <h4 style="font-size:15px;font-weight:700;color:var(--primary-dark)"><i class="fas fa-list" style="color:var(--accent)"></i> Daftar Produk</h4>
        <button type="button" onclick="tambahBaris()" class="btn btn-success btn-sm">
          <i class="fas fa-plus"></i> Tambah Baris
        </button>
      </div>

      <div class="table-wrap">
        <table class="table" id="tblItem">
          <thead><tr>
            <th width="40">No</th>
            <th>Produk</th>
            <th width="120">Harga Beli</th>
            <th width="100">Jumlah</th>
            <th width="130">Subtotal</th>
            <th width="50">Hapus</th>
          </tr></thead>
          <tbody id="itemBody"></tbody>
          <tfoot>
            <tr style="background:#f8fafc;font-weight:700">
              <td colspan="4" style="text-align:right;padding:14px 16px">TOTAL:</td>
              <td id="grandTotal" style="color:var(--success);font-size:16px;padding:14px 16px">Rp 0</td>
              <td></td>
            </tr>
          </tfoot>
        </table>
      </div>

      <div style="display:flex;gap:10px;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
        <button type="submit" class="btn btn-success"><i class="fas fa-save"></i> Simpan Transaksi</button>
        <a href="transaksi_masuk.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
      </div>
    </form>
  </div>
</div>

<script>
const barangData = [
  <?php $barang_list->data_seek(0); while ($b = $barang_list->fetch_assoc()): ?>
  { id: <?= $b['id'] ?>, nama: "<?= addslashes($b['nama_barang']) ?>", harga: <?= $b['harga_beli'] ?>, stok: <?= $b['stok'] ?>, satuan: "<?= $b['satuan'] ?>" },
  <?php endwhile; ?>
];

let rowNo = 0;

function tambahBaris() {
    rowNo++;
    const opts = barangData.map(b => `<option value="${b.id}" data-harga="${b.harga}" data-stok="${b.stok}" data-satuan="${b.satuan}">${b.nama} (Stok: ${b.stok} ${b.satuan})</option>`).join('');
    const html = `
    <tr id="row_${rowNo}">
        <td style="text-align:center;color:var(--text-muted)">${rowNo}</td>
        <td>
            <select name="items[${rowNo}][id_barang]" class="form-control no-icon" style="padding:8px 10px" onchange="updateHarga(this,${rowNo})" required>
                <option value="">-- Pilih Produk --</option>${opts}
            </select>
        </td>
        <td><input type="number" name="items[${rowNo}][harga]" id="harga_${rowNo}" class="form-control no-icon" style="padding:8px 10px" min="0" step="100" onchange="hitungSubtotal(${rowNo})" required></td>
        <td><input type="number" name="items[${rowNo}][jumlah]" id="jml_${rowNo}" class="form-control no-icon" style="padding:8px 10px" min="1" value="1" onchange="hitungSubtotal(${rowNo})" required></td>
        <td id="sub_${rowNo}" style="font-weight:600;color:var(--success)">Rp 0</td>
        <td><button type="button" onclick="hapusBaris(${rowNo})" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>
    </tr>`;
    document.getElementById('itemBody').insertAdjacentHTML('beforeend', html);
}

function updateHarga(sel, n) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.dataset.harga) {
        document.getElementById('harga_' + n).value = opt.dataset.harga;
        hitungSubtotal(n);
    }
}

function hitungSubtotal(n) {
    const h = parseFloat(document.getElementById('harga_' + n).value) || 0;
    const j = parseInt(document.getElementById('jml_' + n).value) || 0;
    const sub = h * j;
    document.getElementById('sub_' + n).textContent = 'Rp ' + sub.toLocaleString('id-ID');
    hitungTotal();
}

function hitungTotal() {
    let total = 0;
    document.querySelectorAll('#itemBody tr').forEach(tr => {
        const h = parseFloat(tr.querySelector('input[name*="[harga]"]')?.value) || 0;
        const j = parseInt(tr.querySelector('input[name*="[jumlah]"]')?.value) || 0;
        total += h * j;
    });
    document.getElementById('grandTotal').textContent = 'Rp ' + total.toLocaleString('id-ID');
}

function hapusBaris(n) {
    const row = document.getElementById('row_' + n);
    if (row) { row.remove(); hitungTotal(); }
}

// Tambah 1 baris awal
tambahBaris();
</script>
<?php include 'footer.php'; ?>