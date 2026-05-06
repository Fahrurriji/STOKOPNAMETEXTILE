<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);
if (!$id) { header('Location: kelola_kategori.php'); exit; }

// Cek apakah digunakan produk
$cek = $conn->query("SELECT COUNT(*) as jml FROM barang WHERE id_kategori=$id");
$row = $cek->fetch_assoc();

if ($row['jml'] > 0) {
    $_SESSION['error'] = "Kategori tidak bisa dihapus karena digunakan oleh <strong>{$row['jml']}</strong> produk!";
} else {
    $kat = $conn->query("SELECT nama_kategori FROM kategori WHERE id=$id")->fetch_assoc();
    if ($conn->query("DELETE FROM kategori WHERE id=$id")) {
        $_SESSION['pesan'] = "Kategori <strong>{$kat['nama_kategori']}</strong> berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus: " . $conn->error;
    }
}

header('Location: kelola_kategori.php');
exit;
?>