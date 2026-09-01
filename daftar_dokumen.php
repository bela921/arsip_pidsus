<?php
require 'config.php';
require 'partials/auth_check.php';
require 'partials/helpers.php';

$kata_kunci = isset($_GET['q']) ? trim($_GET['q']) : '';

if ($kata_kunci !== '') {
    $like = '%' . $kata_kunci . '%';
    $sql = "
        SELECT DISTINCT p.id, p.nomor_perkara, p.judul_perkara, p.nama_tersangka, p.tahap
        FROM perkara p
        LEFT JOIN dokumen d ON d.perkara_id = p.id
        WHERE p.nomor_perkara LIKE ? OR p.judul_perkara LIKE ? OR p.nama_tersangka LIKE ? OR d.jenis_dokumen LIKE ?
        ORDER BY p.tanggal_dibuat DESC
    ";
    $stmt_cari = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt_cari, "ssss", $like, $like, $like, $like);
    mysqli_stmt_execute($stmt_cari);
    $result_perkara = mysqli_stmt_get_result($stmt_cari);
} else {
    $sql = "SELECT id, nomor_perkara, judul_perkara, nama_tersangka, tahap FROM perkara ORDER BY tanggal_dibuat DESC";
    $result_perkara = mysqli_query($conn, $sql);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Berkas — Arsip Pidsus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
    .perkara-header{ display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap; }
    .perkara-title{ font-family:'Source Serif 4', serif; font-size:16.5px; font-weight:600; color:var(--maroon-950); margin:0 0 6px; }
    .perkara-sub{ font-size:13px; color:var(--muted); margin:0 0 12px; }
    .search-bar{ display:flex; gap:8px; margin-bottom:20px; }
    .search-bar input[type="text"]{ flex:1; margin:0; }
</style>
</head>
<body>

<?php require 'partials/navbar.php'; ?>

<div class="page-shell wide">
    <div class="page-head">
        <div>
            <h2 class="serif">Daftar Berkas per Perkara</h2>
            <p>Seluruh perkara beserta dokumen yang telah diunggah</p>
        </div>
        <a class="btn btn-secondary btn-sm" href="tambah_perkara.php">+ Tambah Perkara</a>
    </div>

    <?php if (isset($_GET['hapus'])): ?>
        <?php if ($_GET['hapus'] === 'sukses'): ?>
            <div class="flash sukses">Berhasil dihapus.</div>
        <?php else: ?>
            <div class="flash gagal">Gagal menghapus. Coba lagi.</div>
        <?php endif; ?>
    <?php endif; ?>

    <form method="GET" action="daftar_dokumen.php" class="search-bar">
        <input type="text" name="q" placeholder="Cari nomor perkara, judul, tersangka, atau jenis dokumen..." value="<?= htmlspecialchars($kata_kunci) ?>">
        <button type="submit" class="btn btn-primary btn-sm">Cari</button>
        <?php if ($kata_kunci !== ''): ?>
            <a href="daftar_dokumen.php" class="btn btn-secondary btn-sm">Reset</a>
        <?php endif; ?>
    </form>

    <?php if ($kata_kunci !== '' && mysqli_num_rows($result_perkara) === 0): ?>
        <p class="empty-note">Tidak ada perkara atau dokumen yang cocok dengan pencarian "<?= htmlspecialchars($kata_kunci) ?>".</p>
    <?php endif; ?>

    <?php while ($perkara = mysqli_fetch_assoc($result_perkara)): ?>
        <div class="card">
            <div class="perkara-header">
                <p class="perkara-title">
                    <?= htmlspecialchars($perkara['nomor_perkara']) ?> — <?= htmlspecialchars($perkara['judul_perkara']) ?>
                    <span class="badge <?= kelas_badge_tahap($perkara['tahap']) ?>"><?= htmlspecialchars($perkara['tahap']) ?></span>
                </p>
                <a class="btn btn-danger btn-sm" href="hapus_perkara.php?id=<?= $perkara['id'] ?>" onclick="return confirm('Yakin ingin menghapus perkara ini beserta SEMUA dokumen di dalamnya? Tindakan ini tidak bisa dibatalkan.');">Hapus Perkara</a>
            </div>
            <p class="perkara-sub">Tersangka: <?= htmlspecialchars($perkara['nama_tersangka'] ?: '-') ?></p>

            <?php
            $stmt = mysqli_prepare($conn, "SELECT id, jenis_dokumen, link_unik, tanggal_upload FROM dokumen WHERE perkara_id = ? ORDER BY tanggal_upload DESC");
            mysqli_stmt_bind_param($stmt, "i", $perkara['id']);
            mysqli_stmt_execute($stmt);
            $result_dokumen = mysqli_stmt_get_result($stmt);
            ?>

            <?php if (mysqli_num_rows($result_dokumen) > 0): ?>
                <table class="data-table">
                    <tr>
                        <th>Jenis Dokumen</th>
                        <th>Tanggal</th>
                        <th></th>
                    </tr>
                    <?php while ($dok = mysqli_fetch_assoc($result_dokumen)): ?>
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
</div>

<footer class="pidsus-footer">
    <strong>Kejaksaan Negeri Kota Kediri</strong> — Bidang Tindak Pidana Khusus<br>
    Sistem Arsip Berkas Digital
</footer>

</body>
<script>
    if (window.location.search.includes('hapus=')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>
</html>