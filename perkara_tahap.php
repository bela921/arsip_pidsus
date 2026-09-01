<?php
require 'config.php';
require 'partials/auth_check.php';
require 'partials/helpers.php';

$tahap_valid = ['Pra-Penyelidikan', 'Penyelidikan', 'Penyidikan', 'Penuntutan', 'Eksekusi', 'Selesai'];
$tahap = isset($_GET['tahap']) ? $_GET['tahap'] : '';

if (!in_array($tahap, $tahap_valid)) {
    die("Tahap tidak valid.");
}

$stmt = mysqli_prepare($conn, "SELECT id, nomor_perkara, judul_perkara, nama_tersangka, tanggal_dibuat, keterangan FROM perkara WHERE tahap = ? ORDER BY tanggal_dibuat DESC");
mysqli_stmt_bind_param($stmt, "s", $tahap);
mysqli_stmt_execute($stmt);
$result_perkara = mysqli_stmt_get_result($stmt);
$jumlah = mysqli_num_rows($result_perkara);
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Perkara Tahap <?= htmlspecialchars($tahap) ?> — Arsip Pidsus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
    .perkara-header{ display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; }
    .perkara-title{ font-family:'Source Serif 4', serif; font-size:16.5px; font-weight:600; color:var(--maroon-950); margin:0 0 6px; }
    .perkara-sub{ font-size:13px; color:var(--muted); margin:0 0 8px; }
    .perkara-ket{ font-size:13.5px; color:var(--ink); margin:0 0 14px; }
</style>
</head>
<body>

<?php require 'partials/navbar.php'; ?>

<div class="page-shell">
    <div class="page-head">
        <div>
            <h2 class="serif">Perkara Tahap: <?= htmlspecialchars($tahap) ?></h2>
            <p><?= $jumlah ?> perkara ditemukan pada tahap ini</p>
        </div>
        <a class="btn btn-secondary btn-sm" href="index.php">&larr; Kembali ke beranda</a>
    </div>

    <?php if ($jumlah > 0): ?>
        <?php while ($p = mysqli_fetch_assoc($result_perkara)): ?>
            <div class="card">
                <div class="perkara-header">
                    <p class="perkara-title"><?= htmlspecialchars($p['nomor_perkara']) ?> — <?= htmlspecialchars($p['judul_perkara']) ?></p>
                    <a class="btn btn-danger btn-sm" href="hapus_perkara.php?id=<?= $p['id'] ?>" onclick="return confirm('Yakin ingin menghapus perkara ini beserta SEMUA dokumen di dalamnya? Tindakan ini tidak bisa dibatalkan.');">Hapus Perkara</a>
                </div>
                <p class="perkara-sub">
                    Tersangka: <?= htmlspecialchars($p['nama_tersangka'] ?: '-') ?>
                    &nbsp;|&nbsp; Tanggal: <?= htmlspecialchars($p['tanggal_dibuat']) ?>
                </p>
                <?php if (!empty($p['keterangan'])): ?>
                    <p class="perkara-ket"><?= htmlspecialchars($p['keterangan']) ?></p>
                <?php endif; ?>

                <?php
                $stmt_dok = mysqli_prepare($conn, "SELECT id, jenis_dokumen, link_unik, tanggal_upload FROM dokumen WHERE perkara_id = ? ORDER BY tanggal_upload DESC");
                mysqli_stmt_bind_param($stmt_dok, "i", $p['id']);
                mysqli_stmt_execute($stmt_dok);
                $result_dok = mysqli_stmt_get_result($stmt_dok);
                ?>

                <?php if (mysqli_num_rows($result_dok) > 0): ?>
                    <table class="data-table">
                        <tr>
                            <th>Jenis Dokumen</th>
                            <th>Tanggal</th>
                            <th></th>
                        </tr>
                        <?php while ($dok = mysqli_fetch_assoc($result_dok)): ?>
                            <tr>
                                <td><?= htmlspecialchars($dok['jenis_dokumen']) ?></td>
                                <td><?= htmlspecialchars($dok['tanggal_upload']) ?></td>
                                <td style="text-align:right; white-space:nowrap;">
                                    <a class="btn btn-secondary btn-sm" href="dokumen.php?link=<?= urlencode($dok['link_unik']) ?>" target="_blank">Buka</a>
                                    <a class="btn btn-danger btn-sm" href="hapus_dokumen.php?id=<?= $dok['id'] ?>" onclick="return confirm('Yakin ingin menghapus dokumen ini? Tindakan ini tidak bisa dibatalkan.');">Hapus</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </table>
                <?php else: ?>
                    <p class="empty-note">Belum ada dokumen untuk perkara ini.</p>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p class="empty-note">Belum ada perkara pada tahap ini.</p>
    <?php endif; ?>
</div>

<footer class="pidsus-footer">
    <strong>Kejaksaan Negeri Kota Kediri</strong> — Bidang Tindak Pidana Khusus<br>
    Sistem Arsip Berkas Digital
</footer>

</body>
</html>