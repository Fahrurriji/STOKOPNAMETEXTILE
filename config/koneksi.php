<?php
// =====================================================
// KONFIGURASI KONEKSI DATABASE
// STOK OPNAME INVENTARIS GUDANG TEXTILE
// =====================================================

define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_stokopname'); // Pastikan nama DB sesuai
define('DB_CHARSET', 'utf8mb4');

// --- TAMBAHKAN INI ---
// Sesuaikan "stok_opname" dengan nama folder projek kamu di htdocs
$BASE = "http://localhost/stok_opname/"; 
// ---------------------

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Aktifkan error reporting untuk mempermudah debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Cek apakah MySQLi extension tersedia
if (!extension_loaded('mysqli')) {
    die('<div style="font-family:sans-serif;padding:30px;background:#fff0f0;border:2px solid #e74c3c;border-radius:10px;margin:30px;">
        <h2 style="color:#e74c3c;">⚠️ Extension MySQLi Tidak Tersedia!</h2>
        <p><strong>Error:</strong> PHP MySQLi extension tidak diaktifkan pada server hosting Anda.</p>
        <p style="margin-top:15px;"><strong>Solusi:</strong></p>
        <ul style="margin:10px 0;padding-left:20px;">
            <li>Hubungi penyedia hosting (Rainwail) untuk mengaktifkan extension <code>mysqli</code> atau <code>PDO MySQL</code></li>
            <li>Periksa file <code>php.ini</code> dan pastikan baris berikut tidak dikomen: <code>;extension=mysqli</code></li>
            <li>Jika perlu, minta bantuan admin hosting untuk enable extension</li>
        </ul>
        <p style="margin-top:15px;"><strong>Informasi Server:</strong></p>
        <p>PHP Version: ' . phpversion() . '</p>
        <p>Database Host: ' . DB_HOST . '</p>
    </div>');
}

// Koneksi menggunakan MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek koneksi
if ($conn->connect_error) {
    die('<div style="font-family:sans-serif;padding:30px;background:#fff0f0;border:2px solid #e74c3c;border-radius:10px;margin:30px;">
        <h2 style="color:#e74c3c;">⚠️ Koneksi Database Gagal!</h2>
        <p>Error: ' . $conn->connect_error . '</p>
        <p>Pastikan:</p>
        <ul style="margin:10px 0;padding-left:20px;">
            <li>Server database MySQL/MariaDB sedang berjalan</li>
            <li>Database <strong>' . DB_NAME . '</strong> sudah dibuat</li>
            <li>Kredensial DB_HOST, DB_USER, DB_PASS, dan DB_NAME benar di <code>config/koneksi.php</code></li>
        </ul>
    </div>');
}

$conn->set_charset(DB_CHARSET);

// ... (sisanya tetap sama) ...

// Fungsi helper: escape string
function esc($conn, $str) {
    return $conn->real_escape_string($str);
}

// Fungsi format rupiah
function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

// Fungsi format tanggal
function tgl_indo($tgl) {
    if (empty($tgl)) return '-';
    $bulan = ['', 'Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    $t = explode('-', $tgl);
    return $t[2] . ' ' . $bulan[(int)$t[1]] . ' ' . $t[0];
}

// Fungsi generate kode otomatis
function generate_kode($conn, $table, $field, $prefix, $digit = 4) {
    $sql = "SELECT MAX($field) as last FROM $table WHERE $field LIKE '$prefix%'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    if ($row['last']) {
        $num = (int)substr($row['last'], strlen($prefix)) + 1;
    } else {
        $num = 1;
    }
    return $prefix . str_pad($num, $digit, '0', STR_PAD_LEFT);
}




?>