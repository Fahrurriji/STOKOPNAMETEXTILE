<?php
// 1. Paksa tampilkan error jika ada salah ketik
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/auth.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // A. Ambil detail untuk update stok barang sebelum data dihapus
    $details = $conn->query("SELECT id_barang, jumlah FROM detail_transaksi_masuk WHERE id_transaksi = $id");
    
    if ($details) {
        while ($row = $details->fetch_assoc()) {
            $id_b = $row['id_barang'];
            $qty  = $row['jumlah'];
            // Kurangi stok kembali karena transaksi masuk dibatalkan
            $conn->query("UPDATE barang SET stok = stok - $qty WHERE id = $id_b");
        }
    }

    // B. Hapus Detail Transaksi (Harus duluan agar tidak error Relasi/Foreign Key)
    $hapus_detail = $conn->query("DELETE FROM detail_transaksi_masuk WHERE id_transaksi = $id");
    
    // C. Hapus Transaksi Utama
    $hapus_utama = $conn->query("DELETE FROM transaksi_masuk WHERE id = $id");

    if ($hapus_utama) {
        $_SESSION['pesan'] = "Data transaksi berhasil dihapus.";
    } else {
        $_SESSION['error'] = "Gagal menghapus data: " . $conn->error;
    }
}

// D. Redirect kembali ke halaman list
header("Location: transaksi_masuk.php");
exit;