<?php
require_once '../config/auth.php';
require_once '../config/koneksi.php';
$id = (int)($_GET['id'] ?? 0);
if ($id) {
    $cek = $conn->query("SELECT COUNT(*) as j FROM transaksi_keluar WHERE id_pelanggan=$id")->fetch_assoc();
    if ($cek['j'] > 0) { $_SESSION['error'] = "Pelanggan tidak bisa dihapus, ada {$cek['j']} transaksi terkait!"; }
    else {
        $r = $conn->query("SELECT nama_pelanggan FROM pelanggan WHERE id=$id")->fetch_assoc();
        $conn->query("DELETE FROM pelanggan WHERE id=$id");
        $_SESSION['pesan'] = "Pelanggan <strong>{$r['nama_pelanggan']}</strong> dihapus.";
    }
}
header('Location: kelola_pelanggan.php'); exit;