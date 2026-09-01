<?php
require 'config.php';
require 'partials/auth_check.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: daftar_dokumen.php?hapus=gagal");
    exit;
}

// Ambil dulu path file dan perkara_id-nya sebelum data dihapus dari database
$stmt = mysqli_prepare($conn, "SELECT path_file, perkara_id FROM dokumen WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dokumen = mysqli_fetch_assoc($result);

if (!$dokumen) {
    header("Location: daftar_dokumen.php?hapus=gagal");
    exit;
}

$perkara_id = $dokumen['perkara_id'];

// Hapus baris data di database
$stmt_hapus = mysqli_prepare($conn, "DELETE FROM dokumen WHERE id = ?");
mysqli_stmt_bind_param($stmt_hapus, "i", $id);

try {
    mysqli_stmt_execute($stmt_hapus);
    // Kalau berhasil hapus dari database, hapus juga file fisiknya
    if (file_exists($dokumen['path_file'])) {
        unlink($dokumen['path_file']);
    }

    // Cek apakah perkara ini masih punya dokumen lain yang tersisa
    $stmt_cek = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM dokumen WHERE perkara_id = ?");
    mysqli_stmt_bind_param($stmt_cek, "i", $perkara_id);
    mysqli_stmt_execute($stmt_cek);
    $sisa = mysqli_stmt_get_result($stmt_cek)->fetch_assoc()['total'];

    // Kalau ini dokumen terakhir (sisa = 0), hapus juga perkaranya sekalian
    if ($sisa == 0) {
        $stmt_hapus_perkara = mysqli_prepare($conn, "DELETE FROM perkara WHERE id = ?");
        mysqli_stmt_bind_param($stmt_hapus_perkara, "i", $perkara_id);
        mysqli_stmt_execute($stmt_hapus_perkara);
    }

    header("Location: daftar_dokumen.php?hapus=sukses");
    exit;
} catch (mysqli_sql_exception $e) {
    header("Location: daftar_dokumen.php?hapus=gagal");
    exit;
}
?>