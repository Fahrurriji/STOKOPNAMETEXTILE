<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Tambah Barang Keluar';
$barang_list   = $conn->query("SELECT b.*, k.nama_kategori FROM barang b LEFT JOIN kategori k ON b.id_kategori=k.id WHERE b.status='aktif' AND b.stok > 0 ORDER BY b.nama_barang");
$pelanggan_list= $conn->query("SELECT * FROM pelanggan ORDER BY nama_pelanggan");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan = (int)$_POST['id_pelanggan'];
    $tanggal      = esc($conn, $_POST['tanggal']);
    $diskon       = (float)$_POST['diskon'];
    $keterangan   = esc($conn, $_POST['keterangan']);
    $items        = $_POST['items'] ?? [];

    if (empty($items)) { $error = 'Minimal tambahkan 1 produk!'; }
    else {
        // Validasi stok
        foreach ($items as $item) {
            $id_brg = (int)$item['id_barang'];
            $jml    = (int)$item['jumlah'];
            $stok_skrg = $conn->query("SELECT stok FROM barang WHERE id=$id_brg")->fetch_assoc()['stok'];
            if ($jml > $stok_skrg) {
                $error = "Stok tidak cukup untuk salah satu produk!"; break;
            }
        }
    }

    if (!isset($error)) {
        $no_trans   = generate_kode($conn, 'transaksi_keluar', 'no_transaksi', 'TK-' . date('Ym') . '-');
        $plg_val    = $id_pelanggan ?: 'NULL';
        $total_item = count($items);
        $total_nilai= 0;
        foreach ($items as $item) $total_nilai += $item['jumlah'] * $item['harga'];
        $total_bayar = $total_nilai * (1 - $diskon/100);

        $conn->begin_transaction();
        try {
            $conn->query("INSERT INTO transaksi_keluar (no_transaksi,id_pelanggan,id_admin,tanggal,total_item,total_nilai,diskon,total_bayar,keterangan)
                VALUES ('$no_trans',$plg_val,{$_SESSION['user_id']},'$tanggal',$total_item,$total_nilai,$diskon,$total_bayar,'$keterangan')");
            $id_trans = $conn->insert_id;
            foreach ($items as $item) {
                $id_brg  = (int)$item['id_barang'];
                $jumlah  = (int)$item['jumlah'];
                $harga   = (float)$item['harga'];
                $subtotal= $jumlah * $harga;
                $conn->query("INSERT INTO detail_transaksi_keluar (id_transaksi,id_barang,jumlah,harga_jual,subtotal) VALUES ($id_trans,$id_brg,$jumlah,$harga,$subtotal)");
                $conn->query("UPDATE barang SET stok = stok - $jumlah WHERE id = $id_brg");
            }
            $conn->commit();
            $_SESSION['pesan'] = "Transaksi <strong>$no_trans</strong> berhasil disimpan!";
            header('Location: transaksi_keluar.php'); exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = 'Gagal: ' . $e->getMessage();
        }
    }
}
include '../header.php';
?>
<?php if (isset($error)): ?><div class="alert alert-danger"><i class="fas fa-times-circle"></i> <?= $error ?></div><?php endif; ?>
<div class="card">
  <div class="card-header">
    <div class="card-header-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-arrow-up"></i></div>
    <div><h3>Tambah Transaksi Barang Keluar</h3><p>Catat penjualan kain kepada pelanggan</p></div>
  </div>
  <div class="card-body">
    <form method="POST" id="frmKeluar">
      <div style="display:grid;grid-template-columns:1fr 1fr 1fr 1fr;gap:16px;margin-bottom:24px;padding-bottom:20px;border-bottom:2px dashed var(--border)">
        <div class="form-group">
          <label>Pelanggan</label>
          <div class="input-wrap"><i class="fas fa-user input-icon"></i>
            <select name="id_pelanggan" class="form-control">
              <option value="">-- Pelanggan Umum --</option>
              <?php while ($p = $pelanggan_list->fetch_assoc()): ?>
              <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nama_pelanggan']) ?></option>
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
          <label>Diskon (%)</label>
          <div class="input-wrap"><i class="fas fa-percent input-icon"></i>
            <input type="number" name="diskon" id="diskon" class="form-control" min="0" max="100" step="0.5" value="0" onchange="hitungTotal()">
          </div>
        </div>
        <div class="form-group">
          <label>Keterangan</label>
          <div class="input-wrap"><i class="fas fa-note-sticky input-icon"></i>
            <input type="text" name="keterangan" class="form-control" placeholder="Catatan...">
          </div>
        </div>
      </div>
      <div style="margin-bottom:16px;display:flex;align-items:center;justify-content:space-between">
        <h4 style="font-size:15px;font-weight:700;color:var(--primary-dark)"><i class="fas fa-list" style="color:var(--accent)"></i> Daftar Produk</h4>
        <button type="button" onclick="tambahBaris()" class="btn btn-danger btn-sm"><i class="fas fa-plus"></i> Tambah Baris</button>
      </div>
      <div class="table-wrap">
        <table class="table" id="tblItem">
          <thead><tr><th>No</th><th>Produk</th><th>Harga Jual</th><th>Jumlah</th><th>Subtotal</th><th>Hapus</th></tr></thead>
          <tbody id="itemBody"></tbody>
          <tfoot>
            <tr style="background:#f8fafc;font-weight:600">
              <td colspan="4" style="text-align:right;padding:12px 16px">Subtotal:</td>
              <td id="subTotal" style="padding:12px 16px">Rp 0</td><td></td>
            </tr>
            <tr style="background:#f8fafc;font-weight:600">
              <td colspan="4" style="text-align:right;padding:6px 16px">Diskon:</td>
              <td id="discVal" style="color:var(--danger);padding:6px 16px">- Rp 0</td><td></td>
            </tr>
            <tr style="background:#f8fafc;font-weight:800">
              <td colspan="4" style="text-align:right;padding:12px 16px;color:var(--primary-dark)">TOTAL BAYAR:</td>
              <td id="grandTotal" style="color:var(--success);font-size:16px;padding:12px 16px">Rp 0</td><td></td>
            </tr>
          </tfoot>
        </table>
      </div>
      <div style="display:flex;gap:10px;margin-top:20px;padding-top:20px;border-top:1px solid var(--border)">
        <button type="submit" class="btn btn-danger"><i class="fas fa-save"></i> Simpan Transaksi</button>
        <a href="transaksi_keluar.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Batal</a>
      </div>
    </form>
  </div>
</div>
<script>
const barangData = [
  <?php $barang_list->data_seek(0); while ($b = $barang_list->fetch_assoc()): ?>
  { id: <?= $b['id'] ?>, nama: "<?= addslashes($b['nama_barang']) ?>", harga: <?= $b['harga_jual'] ?>, stok: <?= $b['stok'] ?>, satuan: "<?= $b['satuan'] ?>" },
  <?php endwhile; ?>
];
let rowNo = 0;
function tambahBaris() {
    rowNo++;
    const opts = barangData.map(b => `<option value="${b.id}" data-harga="${b.harga}" data-stok="${b.stok}" data-satuan="${b.satuan}">${b.nama} (Stok: ${b.stok} ${b.satuan})</option>`).join('');
    const html = `<tr id="row_${rowNo}">
        <td style="text-align:center">${rowNo}</td>
        <td><select name="items[${rowNo}][id_barang]" class="form-control no-icon" style="padding:8px 10px" onchange="updateHarga(this,${rowNo})" required><option value="">-- Pilih --</option>${opts}</select></td>
        <td><input type="number" name="items[${rowNo}][harga]" id="h_${rowNo}" class="form-control no-icon" style="padding:8px 10px" min="0" step="100" onchange="hitungSubtotal(${rowNo})" required></td>
        <td><input type="number" name="items[${rowNo}][jumlah]" id="j_${rowNo}" class="form-control no-icon" style="padding:8px 10px" min="1" value="1" onchange="hitungSubtotal(${rowNo})" required></td>
        <td id="s_${rowNo}" style="font-weight:600;color:var(--success)">Rp 0</td>
        <td><button type="button" onclick="hapusBaris(${rowNo})" class="btn btn-danger btn-sm"><i class="fas fa-times"></i></button></td>
    </tr>`;
    document.getElementById('itemBody').insertAdjacentHTML('beforeend', html);
}
function updateHarga(sel, n) {
    const opt = sel.options[sel.selectedIndex];
    if (opt.dataset.harga) { document.getElementById('h_' + n).value = opt.dataset.harga; document.getElementById('j_' + n).max = opt.dataset.stok; hitungSubtotal(n); }
}
function hitungSubtotal(n) {
    const h = parseFloat(document.getElementById('h_' + n).value) || 0;
    const j = parseInt(document.getElementById('j_' + n).value) || 0;
    document.getElementById('s_' + n).textContent = 'Rp ' + (h*j).toLocaleString('id-ID');
    hitungTotal();
}
function hitungTotal() {
    let sub = 0;
    document.querySelectorAll('#itemBody tr').forEach(tr => {
        const h = parseFloat(tr.querySelector('input[name*="[harga]"]')?.value) || 0;
        const j = parseInt(tr.querySelector('input[name*="[jumlah]"]')?.value) || 0;
        sub += h * j;
    });
    const disc = (parseFloat(document.getElementById('diskon').value) || 0) / 100;
    const bayar = sub * (1 - disc);
    document.getElementById('subTotal').textContent   = 'Rp ' + sub.toLocaleString('id-ID');
    document.getElementById('discVal').textContent    = '- Rp ' + (sub * disc).toLocaleString('id-ID');
    document.getElementById('grandTotal').textContent = 'Rp ' + bayar.toLocaleString('id-ID');
}
function hapusBaris(n) { document.getElementById('row_' + n)?.remove(); hitungTotal(); }
tambahBaris();
</script>
<?php include '../footer.php'; ?>