<?php
// PASTIKAN TIDAK ADA SPASI DI ATAS TAG PHP INI
require_once '../config/auth.php';
require_once '../config/koneksi.php';

// Gunakan session_start jika belum dipanggil di auth.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // 1. Cek apakah ada barang yang menggunakan id_pemasok ini
    $cek = $conn->query("SELECT COUNT(*) as j FROM barang WHERE id_pemasok=$id")->fetch_assoc();
    
    if ($cek['j'] > 0) {
        // Jika ada barang, jangan hapus
        $_SESSION['error'] = "Pemasok tidak bisa dihapus, terhubung dengan {$cek['j']} produk!";
    } else {
        // 2. Ambil nama untuk pesan sukses
        $r = $conn->query("SELECT nama_pemasok FROM pemasok WHERE id=$id")->fetch_assoc();
        
        if ($r) {
            // 3. Jalankan perintah hapus
            $conn->query("DELETE FROM pemasok WHERE id=$id");
            $_SESSION['pesan'] = "Pemasok <strong>{$r['nama_pemasok']}</strong> berhasil dihapus.";
        }
    }
}

// 4. Pastikan redirect ke nama file yang benar
header('Location: kelola_pemasok.php'); 
exit;
?>