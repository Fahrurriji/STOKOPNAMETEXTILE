<?php
// 1. Tambahkan reporting error agar jika gagal tidak blank putih
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/auth.php';
require_once '../config/koneksi.php';

$id = (int)($_GET['id'] ?? 0);

if ($id > 0) {
    // A. Ambil nomor transaksi untuk notifikasi pesan nanti
    $check = $conn->query("SELECT no_transaksi FROM transaksi_keluar WHERE id=$id");
    
    if ($check && $check->num_rows > 0) {
        $data_transaksi = $check->fetch_assoc();
        
        $conn->begin_transaction();
        try {
            // B. Ambil detail untuk mengembalikan stok (Barang Keluar dihapus = Stok Kembali bertambah)
            $details = $conn->query("SELECT id_barang, jumlah FROM detail_transaksi_keluar WHERE id_transaksi=$id");
            
            while ($d = $details->fetch_assoc()) {
                $id_b = (int)$d['id_barang'];
                $qty  = (int)$d['jumlah'];
                $conn->query("UPDATE barang SET stok = stok + $qty WHERE id = $id_b");
            }

            // C. HAPUS DETAIL TERLEBIH DAHULU (Ini bagian yang kurang di kode kamu)
            $conn->query("DELETE FROM detail_transaksi_keluar WHERE id_transaksi=$id");

            // D. HAPUS TRANSAKSI UTAMA
            $conn->query("DELETE FROM transaksi_keluar WHERE id=$id");

            $conn->commit();
            $_SESSION['pesan'] = "Transaksi <strong>{$data_transaksi['no_transaksi']}</strong> berhasil dihapus & stok dikembalikan.";

        } catch (Exception $e) {
            $conn->rollback();
            $_SESSION['error'] = "Gagal menghapus: " . $e->getMessage();
        }
    } else {
        $_SESSION['error'] = "Data tidak ditemukan.";
    }
}

// E. Selalu redirect kembali agar tidak stuck di halaman putih
header('Location: transaksi_keluar.php'); 
exit;