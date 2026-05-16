<?php
// =====================================================
// KONFIGURASI KONEKSI DATABASE
// STOK OPNAME INVENTARIS GUDANG TEXTILE
// =====================================================

define('DB_HOST', 'yamabiko.proxy.rlwy.net'); // ← public host
define('DB_PORT', 38702);                      // ← public port
define('DB_USER', 'root');
define('DB_PASS', 'sJJshEUPvfpjpNiEtRcRCyXcXmVlmJJB');
define('DB_NAME', 'railway');
define('DB_CHARSET', 'utf8mb4');;

$BASE = "https://stokopnametextile-production.up.railway.app/login.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

function show_db_error($title, $message) {
    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <title>Database Error</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; padding: 20px; }
        .error-box { background: white; border-left: 5px solid #dc3545; padding: 20px; margin: 20px auto; max-width: 600px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .error-title { color: #dc3545; font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .error-msg { color: #333; line-height: 1.6; }
    </style>
</head>
<body>
    <div class='error-box'>
        <div class='error-title'>⚠️ {$title}</div>
        <div class='error-msg'>{$message}</div>
    </div>
</body>
</html>";
}

$use_pdo = false;
if (!extension_loaded('mysqli')) {
    if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
        $use_pdo = true;
    } else {
        show_db_error('Extension Tidak Tersedia', 'MySQLi dan PDO MySQL tidak tersedia.');
        exit;
    }
}

if ($use_pdo) {
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';port=' . DB_PORT . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]
        );

        // ... (class PDOResultWrapper & DatabaseConnection tetap sama) ...

    } catch (Exception $e) {
        show_db_error('Connection Error', $e->getMessage());
        exit;
    }
} else {
    // MySQLi dengan port
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        show_db_error('Connection Failed', $conn->connect_error);
        exit;
    }
    $conn->set_charset(DB_CHARSET);
}

// Fungsi helper tetap sama
function esc($conn, $str) {
    return $conn->real_escape_string($str);
}

function rupiah($angka) {
    return 'Rp ' . number_format($angka, 0, ',', '.');
}

function tgl_indo($tgl) {
    if (empty($tgl)) return '-';
    $bulan = ['','Januari','Februari','Maret','April','Mei','Juni',
              'Juli','Agustus','September','Oktober','November','Desember'];
    $t = explode('-', $tgl);
    return $t[2] . ' ' . $bulan[(int)$t[1]] . ' ' . $t[0];
}

function generate_kode($conn, $table, $field, $prefix, $digit = 4) {
    $sql = "SELECT MAX($field) as last FROM $table WHERE $field LIKE '$prefix%'";
    $res = $conn->query($sql);
    $row = $res->fetch_assoc();
    $num = $row['last'] ? (int)substr($row['last'], strlen($prefix)) + 1 : 1;
    return $prefix . str_pad($num, $digit, '0', STR_PAD_LEFT);
}
?>