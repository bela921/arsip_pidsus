<?php
require 'config.php';
require 'partials/auth_check.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Tambah Perkara — Arsip Pidsus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php require 'partials/navbar.php'; ?>

<div class="page-shell">
    <div class="page-head">
        <div>
            <h2 class="serif">Tambah Perkara Baru</h2>
            <p>Daftarkan perkara agar dapat dipilih saat mengunggah dokumen</p>
        </div>
    </div>

    <?php if (isset($_GET['status'])): ?>
        <?php if ($_GET['status'] === 'gagal'): ?>
            <div class="flash gagal">Gagal menambahkan perkara. Pastikan nomor perkara belum pernah dipakai sebelumnya.</div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="card">
        <form action="proses_tambah_perkara.php" method="POST">

            <label for="nomor_perkara">Nomor Perkara</label>
            <input type="text" name="nomor_perkara" id="nomor_perkara" placeholder="Contoh: PDS-05/2026" required>

            <label for="judul_perkara">Judul Perkara</label>
            <input type="text" name="judul_perkara" id="judul_perkara" placeholder="Contoh: Korupsi Dana Desa Sukorame" required>

            <label for="nama_tersangka">Nama Tersangka</label>
            <input type="text" name="nama_tersangka" id="nama_tersangka" placeholder="Nama tersangka (opsional)">

            <label for="tahap">Tahap Penanganan</label>
            <select name="tahap" id="tahap">
                <option value="Pra-Penyelidikan">Pra-Penyelidikan</option>
                <option value="Penyelidikan">Penyelidikan</option>
                <option value="Penyidikan">Penyidikan</option>
                <option value="Penuntutan">Penuntutan</option>
                <option value="Eksekusi">Eksekusi</option>
                <option value="Selesai">Selesai</option>
            </select>

            <label for="tanggal_dibuat">Tanggal</label>
            <input type="date" name="tanggal_dibuat" id="tanggal_dibuat" required>

            <label for="keterangan">Keterangan</label>
            <textarea name="keterangan" id="keterangan" placeholder="Catatan tambahan (opsional)"></textarea>

            <div style="margin-top:24px;">
                <button type="submit" class="btn btn-primary">Simpan Perkara</button>
            </div>
        </form>
    </div>
</div>

<footer class="pidsus-footer">
    <strong>Kejaksaan Negeri Kota Kediri</strong> — Bidang Tindak Pidana Khusus<br>
    Sistem Arsip Berkas Digital
</footer>

</body>
</html>