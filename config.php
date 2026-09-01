<?php
// =========================================================
// File Koneksi Database
// Sistem Arsip Berkas Digital Pidsus
// =========================================================

// Mulai session, dibutuhkan untuk sistem login
session_start();

// Cegah browser nyimpen cache halaman, biar data selalu yang terbaru
// (mencegah tampilan "lama" muncul lagi setelah tambah/hapus data)
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

$host = "127.0.0.1"; // diganti dari "localhost" biar tidak nyangkut karena masalah IPv6/IPv4
$user = "root";       // default XAMPP, ganti kalau kamu pakai user MySQL lain
$pass = "";           // default XAMPP kosong, ganti kalau kamu set password
$dbname = "arsip_pidsus";

$conn = mysqli_connect($host, $user, $pass, $dbname);

// Cek koneksi
if (!$conn) {
    die("Koneksi database gagal: " . mysqli_connect_error());
}

// Set charset biar aman untuk teks bahasa Indonesia
mysqli_set_charset($conn, "utf8mb4");
?>