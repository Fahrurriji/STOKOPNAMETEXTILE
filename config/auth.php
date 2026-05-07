<?php
// Cek sesi login
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    // Hitung BASE URL
    $script_url = str_replace('\\', '/', $_SERVER['PHP_SELF']);
    $project_folder = 'STOKOPNAMETEXTILE';
    $parts = explode('/', trim($script_url, '/'));
    $base_index = array_search($project_folder, $parts);
    if ($base_index !== false) {
        $BASE = '/' . implode('/', array_slice($parts, 0, $base_index + 1)) . '/';
    } else {
        $BASE = '/' . $project_folder . '/';
    }
    header('Location: ' . $BASE . 'login.php');
    exit;
}

function cek_admin() {
    if ($_SESSION['role'] !== 'admin') {
        $script_url = str_replace('\\', '/', $_SERVER['PHP_SELF']);
        $parts = explode('/', trim($script_url, '/'));
        $base_index = array_search('STOKOPNAMETEXTILE', $parts);
        $BASE = ($base_index !== false) ? '/' . implode('/', array_slice($parts, 0, $base_index + 1)) . '/' : '/STOKOPNAMETEXTILE/';
        header('Location: ' . $BASE . 'index.php');
        exit;
    }
}
?>