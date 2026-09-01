<?php
require 'config.php';
require 'partials/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nomor_perkara  = trim($_POST['nomor_perkara']);
    $judul_perkara  = trim($_POST['judul_perkara']);
    $nama_tersangka = trim($_POST['nama_tersangka']);
    $tahap          = $_POST['tahap'];
    $tanggal_dibuat = $_POST['tanggal_dibuat'];
    $keterangan     = trim($_POST['keterangan']);

    // Validasi dasar: field wajib tidak boleh kosong
    if (empty($nomor_perkara) || empty($judul_perkara) || empty($tanggal_dibuat)) {
        header("Location: tambah_perkara.php?status=gagal");
        exit;
    }

    $stmt = mysqli_prepare($conn, "INSERT INTO perkara (nomor_perkara, judul_perkara, nama_tersangka, tahap, tanggal_dibuat, keterangan) VALUES (?, ?, ?, ?, ?, ?)");
    mysqli_stmt_bind_param($stmt, "ssssss", $nomor_perkara, $judul_perkara, $nama_tersangka, $tahap, $tanggal_dibuat, $keterangan);

    try {
        mysqli_stmt_execute($stmt);
        $id_perkara_baru = mysqli_insert_id($conn);
        header("Location: upload.php?status=perkara_sukses&perkara_id=" . $id_perkara_baru);
        exit;
    } catch (mysqli_sql_exception $e) {
        // Kemungkinan besar karena nomor_perkara sudah ada (kolom UNIQUE)
        header("Location: tambah_perkara.php?status=gagal");
        exit;
    }

} else {
    header("Location: tambah_perkara.php");
    exit;
}
?>