<?php
require 'config.php';
require 'partials/auth_check.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    header("Location: daftar_dokumen.php?hapus=gagal");
    exit;
}

// Ambil dulu semua path file dokumen yang terkait perkara ini, sebelum datanya dihapus
$stmt = mysqli_prepare($conn, "SELECT path_file FROM dokumen WHERE perkara_id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$daftar_file = [];
while ($row = mysqli_fetch_assoc($result)) {
    $daftar_file[] = $row['path_file'];
}

// Hapus perkara. Karena tabel dokumen punya ON DELETE CASCADE,
// semua dokumen yang terhubung ke perkara ini otomatis ikut terhapus dari database.
$stmt_hapus = mysqli_prepare($conn, "DELETE FROM perkara WHERE id = ?");
mysqli_stmt_bind_param($stmt_hapus, "i", $id);

try {
    mysqli_stmt_execute($stmt_hapus);

    // Hapus juga file fisiknya satu per satu
    foreach ($daftar_file as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    header("Location: daftar_dokumen.php?hapus=sukses");
    exit;
} catch (mysqli_sql_exception $e) {
    header("Location: daftar_dokumen.php?hapus=gagal");
    exit;
}
?>