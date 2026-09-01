<?php
require 'config.php';
require 'partials/auth_check.php';

// Cek apakah request-nya POST dan ada file yang dikirim
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file_dokumen'])) {

    $perkara_id     = $_POST['perkara_id'];
    $jenis_dokumen  = $_POST['jenis_dokumen'];

    $folder_tujuan = 'uploads/';
    if (!is_dir($folder_tujuan)) {
        mkdir($folder_tujuan, 0755, true);
    }

    $nama_file_array = $_FILES['file_dokumen']['name'];
    $tmp_path_array  = $_FILES['file_dokumen']['tmp_name'];
    $error_array     = $_FILES['file_dokumen']['error'];

    $jumlah_berhasil = 0;
    $jumlah_gagal    = 0;

    // Loop untuk memproses setiap file yang diupload satu per satu
    foreach ($nama_file_array as $index => $nama_file_asli) {

        $tmp_path = $tmp_path_array[$index];
        $error    = $error_array[$index];

        // Kalau slot ini kosong (tidak ada file dipilih), lewati saja
        if ($error === UPLOAD_ERR_NO_FILE) {
            continue;
        }

        // Validasi dasar: pastikan tidak ada error saat upload
        if ($error !== UPLOAD_ERR_OK) {
            $jumlah_gagal++;
            continue;
        }

        // Validasi: hanya izinkan file PDF
        $ekstensi = strtolower(pathinfo($nama_file_asli, PATHINFO_EXTENSION));
        if ($ekstensi !== 'pdf') {
            $jumlah_gagal++;
            continue;
        }

        // Buat nama file unik di server biar tidak bentrok kalau ada nama sama
        $nama_file_server = uniqid('dok_', true) . '.pdf';
        $path_tujuan = $folder_tujuan . $nama_file_server;

        // Pindahkan file dari lokasi sementara ke folder uploads/
        if (!move_uploaded_file($tmp_path, $path_tujuan)) {
            $jumlah_gagal++;
            continue;
        }

        // Generate link unik untuk akses dokumen ini
        $link_unik = bin2hex(random_bytes(16));

        // Simpan data ke database
        $stmt = mysqli_prepare($conn, "INSERT INTO dokumen (perkara_id, jenis_dokumen, nama_file, path_file, link_unik) VALUES (?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "issss", $perkara_id, $jenis_dokumen, $nama_file_asli, $path_tujuan, $link_unik);

        try {
            mysqli_stmt_execute($stmt);
            $jumlah_berhasil++;
        } catch (mysqli_sql_exception $e) {
            // Kalau gagal simpan ke database, hapus lagi file yang sudah terlanjur ke-upload
            if (file_exists($path_tujuan)) {
                unlink($path_tujuan);
            }
            $jumlah_gagal++;
        }
    }

    // Tentukan halaman tujuan berdasarkan hasil akhir
    if ($jumlah_berhasil > 0 && $jumlah_gagal === 0) {
        header("Location: index.php?status=upload_sukses&jumlah=" . $jumlah_berhasil);
        exit;
    } elseif ($jumlah_berhasil > 0 && $jumlah_gagal > 0) {
        header("Location: upload.php?status=sebagian_gagal");
        exit;
    } else {
        header("Location: upload.php?status=gagal");
        exit;
    }

} else {
    header("Location: upload.php");
    exit;
}
?>