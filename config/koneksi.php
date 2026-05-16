<?php
// =====================================================
// KONFIGURASI KONEKSI DATABASE
// STOK OPNAME INVENTARIS GUDANG TEXTILE
// =====================================================

define('DB_HOST', 'mysql.railway.internal');
define('DB_USER', 'root');
define('DB_PASS', 'sJJshEUPvfpjpNiEtRcRCyXcXmVlmJJB');
define('DB_NAME', 'railway'); // Pastikan nama DB sesuai
define('DB_CHARSET', 'utf8mb4');

// --- TAMBAHKAN INI ---
// Sesuaikan "stok_opname" dengan nama folder projek kamu di htdocs
$BASE = "https://stokopnametextile-production.up.railway.app//login.php"; 
// ---------------------

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Aktifkan error reporting untuk mempermudah debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Function untuk menampilkan error dengan format rapi
function show_db_error($title, $message) {
    echo "<!DOCTYPE html>
<html>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Error</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
        .error-box { background: white; border-left: 5px solid #dc3545; padding: 20px; margin: 20px auto; max-width: 600px; border-radius: 5px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .error-title { color: #dc3545; font-size: 20px; font-weight: bold; margin-bottom: 10px; }
        .error-msg { color: #333; line-height: 1.6; }
        code { background: #f0f0f0; padding: 2px 5px; border-radius: 3px; }
    </style>
</head>
<body>
    <div class='error-box'>
        <div class='error-title'>⚠️ " . $title . "</div>
        <div class='error-msg'>" . $message . "</div>
    </div>
</body>
</html>";
}

// Cek MySQLi atau PDO MySQL
$use_pdo = false;
if (!extension_loaded('mysqli')) {
    if (extension_loaded('pdo') && extension_loaded('pdo_mysql')) {
        $use_pdo = true;
    } else {
        show_db_error(
            'Extension Database Tidak Tersedia',
            '<strong>Error:</strong> PHP MySQLi dan PDO MySQL extension tidak tersedia.<br><br>
            <strong>Hubungi Rainwail support untuk mengaktifkan:</strong><br>
            • extension=mysqli<br>
            • atau extension=pdo_mysql<br><br>
            <strong>Info Server:</strong> PHP ' . phpversion() . ' | Host: ' . DB_HOST
        );
        exit;
    }
}

// Koneksi Database
if ($use_pdo) {
    // Menggunakan PDO
    try {
        $pdo = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET,
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT]
        );
        
        // Wrapper Result Set
        class PDOResultWrapper {
            private $stmt;
            private $rows = [];
            private $position = 0;
            private $row_count = 0;
            
            public function __construct($stmt) {
                $this->stmt = $stmt;
                if ($stmt instanceof PDOStatement) {
                    $this->rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    $this->row_count = count($this->rows);
                }
            }
            
            public function fetch_assoc() {
                if ($this->position < $this->row_count) {
                    return $this->rows[$this->position++];
                }
                return null;
            }
            
            public function __get($name) {
                if ($name === 'num_rows') return $this->row_count;
                return null;
            }
            
            public function data_seek($offset) {
                if ($offset >= 0 && $offset < $this->row_count) {
                    $this->position = $offset;
                    return true;
                }
                return false;
            }
        }
        
        // Wrapper Connection
        class DatabaseConnection {
            private $pdo;
            public $error = '';
            private $use_pdo = true;
            
            public function __construct($pdo) { 
                $this->pdo = $pdo; 
            }
            
            public function query($sql) {
                try {
                    $stmt = $this->pdo->query($sql);
                    if ($stmt === false) {
                        $error_info = $this->pdo->errorInfo();
                        $this->error = isset($error_info[2]) ? $error_info[2] : 'Unknown error';
                        return false;
                    }
                    return new PDOResultWrapper($stmt);
                } catch (Exception $e) {
                    $this->error = $e->getMessage();
                    return false;
                }
            }
            
            public function prepare($sql) { 
                return $this->pdo->prepare($sql); 
            }
            
            public function escape_string($str) { 
                return str_replace("'", "''", $str); 
            }
            
            public function set_charset($charset) {}
            
            public function affected_rows() { 
                return 0; 
            }
            
            public function insert_id() { 
                return 0; 
            }
            
            public function close() { 
                $this->pdo = null; 
            }
        }
        $conn = new DatabaseConnection($pdo);
    } catch (Exception $e) {
        show_db_error('Database Connection Error', 
            '<strong>PDO Error:</strong> ' . $e->getMessage() . '<br><br>' .
            'Host: ' . DB_HOST . '<br>' .
            'Database: ' . DB_NAME
        );
        exit;
    }
} else {
    // Menggunakan MySQLi
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        show_db_error('Database Connection Failed',
            '<strong>MySQLi Error:</strong> ' . $conn->connect_error . '<br><br>' .
            'Pastikan MySQL/MariaDB berjalan dan database <strong>' . DB_NAME . '</strong> sudah dibuat.'
        );
        exit;
    }
    $conn->set_charset(DB_CHARSET);
}

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