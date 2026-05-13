<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$page_title = 'Laporan Transaksi';

$tgl_awal  = $_GET['tgl_awal']  ?? date('Y-m-01');
$tgl_akhir = $_GET['tgl_akhir'] ?? date('Y-m-d');
$jenis     = $_GET['jenis']     ?? 'semua';

// Data transaksi masuk
$masuk = null; $total_masuk = 0;
if ($jenis === 'semua' || $jenis === 'masuk') {
    $masuk = $conn->query("SELECT tm.*, p.nama_pemasok, a.nama_lengkap as admin_name
        FROM transaksi_masuk tm
        LEFT JOIN pemasok p ON tm.id_pemasok = p.id
        LEFT JOIN admin a ON tm.id_admin = a.id
        WHERE tm.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'
        ORDER BY tm.tanggal DESC, tm.id DESC");
    $r = $conn->query("SELECT COALESCE(SUM(total_nilai),0) as tot FROM transaksi_masuk WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    $total_masuk = $r->fetch_assoc()['tot'];
}

// Data transaksi keluar
$keluar = null; $total_keluar = 0;
if ($jenis === 'semua' || $jenis === 'keluar') {
    $keluar = $conn->query("SELECT tk.*, pl.nama_pelanggan, a.nama_lengkap as admin_name
        FROM transaksi_keluar tk
        LEFT JOIN pelanggan pl ON tk.id_pelanggan = pl.id
        LEFT JOIN admin a ON tk.id_admin = a.id
        WHERE tk.tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'
        ORDER BY tk.tanggal DESC, tk.id DESC");
    $r = $conn->query("SELECT COALESCE(SUM(total_bayar),0) as tot FROM transaksi_keluar WHERE tanggal BETWEEN '$tgl_awal' AND '$tgl_akhir'");
    $total_keluar = $r->fetch_assoc()['tot'];
}

$laba = $total_keluar - $total_masuk;

include '../header.php';
?>

<!-- Filter Form -->
<div class="card no-print" style="margin-bottom:20px">
    <div class="card-header">
        <div class="card-header-icon"><i class="fas fa-file-invoice"></i></div>
        <div><h3>Laporan Transaksi</h3><p>Rekap barang masuk &amp; keluar gudang</p></div>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="fas fa-print"></i> Cetak
        </button>
    </div>
    <div class="card-body">
        <form method="GET" style="display:flex;gap:14px;flex-wrap:wrap;align-items:flex-end">
            <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Dari Tanggal</label>
                <input type="date" name="tgl_awal" class="form-control no-icon" style="padding:9px 12px;width:180px" value="<?= $tgl_awal ?>">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Sampai Tanggal</label>
                <input type="date" name="tgl_akhir" class="form-control no-icon" style="padding:9px 12px;width:180px" value="<?= $tgl_akhir ?>">
            </div>
            <div>
                <label style="font-size:12px;font-weight:600;display:block;margin-bottom:4px">Jenis Transaksi</label>
                <div class="input-wrap">
                    <i class="fas fa-exchange-alt input-icon"></i>
                    <select name="jenis" class="form-control" style="width:180px">
                        <option value="semua"  <?= $jenis==='semua'  ? 'selected':'' ?>>Semua Transaksi</option>
                        <option value="masuk"  <?= $jenis==='masuk'  ? 'selected':'' ?>>Barang Masuk Saja</option>
                        <option value="keluar" <?= $jenis==='keluar' ? 'selected':'' ?>>Barang Keluar Saja</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary btn-sm" style="margin-bottom:2px">
                <i class="fas fa-filter"></i> Tampilkan
            </button>
        </form>
    </div>
</div>

<!-- Print Header -->
<div class="print-only" style="display:none;text-align:center;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #1F3864">
    <h2 style="font-size:18px;font-weight:700;margin-bottom:4px">LAPORAN TRANSAKSI GUDANG TEXTILE</h2>
    <p style="font-size:12px;color:#666">Periode: <?= tgl_indo($tgl_awal) ?> s/d <?= tgl_indo($tgl_akhir) ?> &nbsp;|&nbsp; Dicetak: <?= tgl_indo(date('Y-m-d')) ?> &nbsp;|&nbsp; Oleh: <?= htmlspecialchars($_SESSION['nama_lengkap']) ?></p>
</div>

<!-- Ringkasan -->
<div class="stats-grid no-print" style="margin-bottom:20px">
    <div class="stat-card green">
        <div class="stat-icon"><i class="fas fa-arrow-circle-down"></i></div>
        <div class="stat-info">
            <div class="si-value" style="font-size:18px"><?= rupiah($total_masuk) ?></div>
            <div class="si-label">Total Pembelian (Masuk)</div>
        </div>
    </div>
    <div class="stat-card red">
        <div class="stat-icon"><i class="fas fa-arrow-circle-up"></i></div>
        <div class="stat-info">
            <div class="si-value" style="font-size:18px"><?= rupiah($total_keluar) ?></div>
            <div class="si-label">Total Penjualan (Keluar)</div>
        </div>
    </div>
    <div class="stat-card <?= $laba >= 0 ? 'gold' : 'blue' ?>">
        <div class="stat-icon"><i class="fas fa-calculator"></i></div>
        <div class="stat-info">
            <div class="si-value" style="font-size:18px;color:<?= $laba >= 0 ? 'var(--success)' : 'var(--danger)' ?>">
                <?= ($laba >= 0 ? '+' : '') . rupiah($laba) ?>
            </div>
            <div class="si-label">Selisih (Penjualan - Pembelian)</div>
        </div>
    </div>
</div>

<!-- Tabel Barang Masuk -->
<?php if ($masuk && ($jenis === 'semua' || $jenis === 'masuk')): ?>
<div class="card" style="margin-bottom:24px">
    <div class="card-header">
        <div class="card-header-icon" style="background:#dcfce7;color:#16a34a"><i class="fas fa-arrow-down"></i></div>
        <div>
            <h3>Transaksi Barang Masuk</h3>
            <p>Periode: <?= tgl_indo($tgl_awal) ?> s/d <?= tgl_indo($tgl_akhir) ?></p>
        </div>
        <span class="badge badge-success" style="font-size:13px;padding:6px 14px"><?= $masuk->num_rows ?> transaksi</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>No</th><th>No. Transaksi</th><th>Tanggal</th><th>Pemasok</th><th>Total Item</th><th>Nilai</th><th>Admin</th></tr>
            </thead>
            <tbody>
            <?php if ($masuk->num_rows === 0): ?>
                <tr><td colspan="7" style="text-align:center;padding:24px;color:var(--text-muted)">Tidak ada transaksi masuk periode ini</td></tr>
            <?php else: $no=1; while ($r = $masuk->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-success"><?= htmlspecialchars($r['no_transaksi']) ?></span></td>
                    <td><?= tgl_indo($r['tanggal']) ?></td>
                    <td><?= htmlspecialchars($r['nama_pemasok'] ?? 'Tanpa Pemasok') ?></td>
                    <td><span class="badge badge-primary"><?= $r['total_item'] ?> item</span></td>
                    <td><strong><?= rupiah($r['total_nilai']) ?></strong></td>
                    <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($r['admin_name']) ?></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700">
                    <td colspan="5" style="text-align:right;padding:12px 16px">Total Pembelian:</td>
                    <td style="color:var(--success);padding:12px 16px"><?= rupiah($total_masuk) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Tabel Barang Keluar -->
<?php if ($keluar && ($jenis === 'semua' || $jenis === 'keluar')): ?>
<div class="card">
    <div class="card-header">
        <div class="card-header-icon" style="background:#fee2e2;color:#dc2626"><i class="fas fa-arrow-up"></i></div>
        <div>
            <h3>Transaksi Barang Keluar</h3>
            <p>Periode: <?= tgl_indo($tgl_awal) ?> s/d <?= tgl_indo($tgl_akhir) ?></p>
        </div>
        <span class="badge badge-danger" style="font-size:13px;padding:6px 14px"><?= $keluar->num_rows ?> transaksi</span>
    </div>
    <div class="table-wrap">
        <table class="table">
            <thead>
                <tr><th>No</th><th>No. Transaksi</th><th>Tanggal</th><th>Pelanggan</th><th>Total Item</th><th>Diskon</th><th>Total Bayar</th><th>Admin</th></tr>
            </thead>
            <tbody>
            <?php if ($keluar->num_rows === 0): ?>
                <tr><td colspan="8" style="text-align:center;padding:24px;color:var(--text-muted)">Tidak ada transaksi keluar periode ini</td></tr>
            <?php else: $no=1; while ($r = $keluar->fetch_assoc()): ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><span class="badge badge-danger"><?= htmlspecialchars($r['no_transaksi']) ?></span></td>
                    <td><?= tgl_indo($r['tanggal']) ?></td>
                    <td><?= htmlspecialchars($r['nama_pelanggan'] ?? 'Umum') ?></td>
                    <td><span class="badge badge-primary"><?= $r['total_item'] ?> item</span></td>
                    <td><?= $r['diskon'] > 0 ? $r['diskon'].'%' : '-' ?></td>
                    <td><strong style="color:var(--success)"><?= rupiah($r['total_bayar']) ?></strong></td>
                    <td style="font-size:12px;color:var(--text-muted)"><?= htmlspecialchars($r['admin_name']) ?></td>
                </tr>
            <?php endwhile; endif; ?>
            </tbody>
            <tfoot>
                <tr style="background:#f8fafc;font-weight:700">
                    <td colspan="6" style="text-align:right;padding:12px 16px">Total Penjualan:</td>
                    <td style="color:var(--success);padding:12px 16px"><?= rupiah($total_keluar) ?></td>
                    <td></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- Ringkasan Print -->
<div class="print-only" style="display:none;margin-top:20px;padding:12px;border:1px solid #ddd;border-radius:8px">
    <table style="width:100%;font-size:13px">
        <tr>
            <td style="padding:6px"><strong>Total Pembelian:</strong></td>
            <td style="color:green;font-weight:700"><?= rupiah($total_masuk) ?></td>
            <td style="padding:6px"><strong>Total Penjualan:</strong></td>
            <td style="color:green;font-weight:700"><?= rupiah($total_keluar) ?></td>
            <td style="padding:6px"><strong>Selisih:</strong></td>
            <td style="font-weight:700;color:<?= $laba >= 0 ? 'green' : 'red' ?>"><?= ($laba >= 0 ? '+':'') . rupiah($laba) ?></td>
        </tr>
    </table>
</div>

<style>
@media print {
    .no-print { display: none !important; }
    .print-only { display: block !important; }
    .sidebar, .topbar { display: none !important; }
    .main-content { margin-left: 0 !important; }
    .page-content { padding: 10px !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd; margin-bottom: 16px; }
    body { font-size: 11px !important; }
    .badge { border: 1px solid #ccc; }
}
</style>

<?php include '../footer.php'; ?>