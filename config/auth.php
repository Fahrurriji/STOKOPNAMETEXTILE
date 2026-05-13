<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// PAKSA nama folder proyek yang benar
$project_folder = ''; 

// Membuat konstanta BASEURL yang aman
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
define('BASEURL', $protocol . $host . '/' . $project_folder . '/');

// Proteksi Login
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . BASEURL . 'login.php');
    exit;
}

function cek_admin() {
    if ($_SESSION['role'] !== 'admin') {
        header('Location: ' . BASEURL . 'index.php');
        exit;
    }
}