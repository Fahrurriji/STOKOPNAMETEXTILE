<?php
// =====================================================
// KONFIGURASI KONEKSI DATABASE (CLOUD-READY)
// =====================================================

// Mengambil variabel dari Railway Environment Variables
define('DB_HOST', getenv('DB_HOST'));
define('DB_USER', getenv('DB_USER'));
define('DB_PASS', getenv('DB_PASS'));
define('DB_NAME', getenv('DB_NAME'));
define('DB_PORT', getenv('DB_PORT') ?: '3306'); // Port default MySQL
define('DB_CHARSET', 'utf8mb4');

// URL Base dinamis
$BASE = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . "/";

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Koneksi menggunakan MySQLi
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);

// Cek koneksi
if ($conn->connect_error) {
    die('Koneksi Database Gagal: ' . $conn->connect_error);
}

$conn->set_charset(DB_CHARSET);

// ... (sisa fungsi helper-mu tetap sama) ...

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