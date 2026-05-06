<?php
// Cek sesi login
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    // Tentukan path ke login.php
    $depth = '';
    $dir = basename(dirname($_SERVER['PHP_SELF']));
    if ($dir !== 'STOKOPNAMETEXTILE' && $dir !== '.') {
        $depth = '../';
    }
    header('Location: ' . $depth . 'login.php');
    exit;
}

// Cek role admin
function cek_admin() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ../index.php');
        exit;
    }
}
?>