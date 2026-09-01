<?php
require 'config.php';
require 'partials/auth_check.php';
require 'partials/helpers.php';

$total_perkara = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM perkara"))['total'];
$total_dokumen = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM dokumen"))['total'];
$total_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM perkara WHERE tahap = 'Selesai'"))['total'];

$daftar_tahap = ['Pra-Penyelidikan', 'Penyelidikan', 'Penyidikan', 'Penuntutan', 'Eksekusi', 'Selesai'];
$jumlah_per_tahap = [];
foreach ($daftar_tahap as $t) {
    $stmt_tahap = mysqli_prepare($conn, "SELECT COUNT(*) as total FROM perkara WHERE tahap = ?");
    mysqli_stmt_bind_param($stmt_tahap, "s", $t);
    mysqli_stmt_execute($stmt_tahap);
    $jumlah_per_tahap[$t] = mysqli_stmt_get_result($stmt_tahap)->fetch_assoc()['total'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Sistem Arsip Berkas Digital — Kejaksaan Negeri Kota Kediri</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
    .hero{
        max-width:1040px;
        margin:0 auto;
        padding:52px 32px 36px;
        text-align:center;
        position:relative;
        overflow:hidden;
    }
    .hero-watermark{
        position:absolute;
        top:50%;
        left:50%;
        transform:translate(-50%,-50%);
        height:520px;
        width:auto;
        opacity:.05;
        pointer-events:none;
        z-index:0;
        user-select:none;
    }
    .hero-content{
        position:relative;
        z-index:1;
    }
    .flash-wrap{ max-width:1040px; margin:20px auto 0; padding:0 32px; }
    .hero-kicker{
        display:inline-block;
        font-size:12px;
        letter-spacing:.16em;
        text-transform:uppercase;
        color:var(--gold-600);
        border:1px solid var(--gold-500);
        padding:5px 14px;
        border-radius:2px;
        margin-bottom:22px;
    }
    .hero h2{
        font-size:36px;
        font-weight:600;
        margin:0 0 6px;
        color:var(--maroon-950);
        letter-spacing:-.01em;
    }
    .hero .hero-instansi{
        font-size:26px;
        font-weight:700;
        color:var(--maroon-950);
        margin:0 auto 20px;
        text-align:center;
        letter-spacing:.005em;
    }
    .hero p{
        max-width:640px;
        margin:0 auto;
        color:var(--muted);
        font-size:15.5px;
        text-wrap:balance;
    }
    .stats{
        max-width:1040px;
        margin:0 auto;
        padding:0 32px 40px;
        display:grid;
        grid-template-columns:repeat(3, 1fr);
        gap:18px;
    }
    .stat-card{
        background:var(--paper);
        border:1px solid var(--line);
        border-top:3px solid var(--gold-500);
        padding:22px 20px;
        text-align:center;
    }
    .stat-number{
        font-family:'Source Serif 4', serif;
        font-size:34px;
        font-weight:700;
        color:var(--maroon-900);
        line-height:1;
    }
    .stat-label{
        margin-top:8px;
        font-size:12.5px;
        letter-spacing:.06em;
        text-transform:uppercase;
        color:var(--muted);
    }
    .tahap-section{ max-width:1040px; margin:0 auto; padding:0 32px 40px; }
    .tahap-section h3{
        font-family:'Source Serif 4', serif;
        font-size:19px;
        margin:0 0 16px;
        color:var(--maroon-950);
    }
    .tahap-grid{ display:grid; grid-template-columns:repeat(6, 1fr); gap:12px; }
    .tahap-card{
        display:block;
        text-decoration:none;
        background:var(--paper);
        border:1px solid var(--line);
        border-radius:3px;
        padding:16px 10px;
        text-align:center;
        transition:border-color .15s ease, transform .1s ease;
    }
    .tahap-card:hover{ border-color:var(--gold-500); transform:translateY(-1px); }
    .tahap-count{
        font-family:'Source Serif 4', serif;
        font-size:24px;
        font-weight:700;
        color:var(--maroon-900);
        line-height:1;
        margin-bottom:6px;
    }
    .tahap-name{ font-size:11.5px; color:var(--muted); line-height:1.3; }
    @media (max-width:820px){ .tahap-grid{ grid-template-columns:repeat(3, 1fr); } }
    @media (max-width:480px){ .tahap-grid{ grid-template-columns:repeat(2, 1fr); } }

    .actions{ max-width:1040px; margin:0 auto; padding:0 32px 56px; display:grid; grid-template-columns:1fr 1fr; gap:18px; }
    .action-card{
        display:flex; align-items:center; gap:16px;
        background:var(--maroon-900); color:#fff; text-decoration:none;
        padding:22px 24px; border-radius:3px; transition:background .15s ease;
    }
    .action-card:hover{ background:var(--maroon-800); }
    .action-card.secondary{ background:var(--paper); color:var(--maroon-950); border:1px solid var(--line); }
    .action-card.secondary:hover{ background:var(--gold-100); }
    .action-icon{
        flex-shrink:0; width:42px; height:42px; border-radius:50%;
        display:flex; align-items:center; justify-content:center; background:rgba(255,255,255,.14);
    }
    .action-card.secondary .action-icon{ background:var(--cream); }
    .action-title{ font-weight:600; font-size:15px; margin:0 0 2px; }
    .action-sub{ font-size:12.5px; opacity:.8; margin:0; }

    @media (max-width:640px){
        .stats{ grid-template-columns:1fr; }
        .actions{ grid-template-columns:1fr; }
        .hero h2{ font-size:26px; }
    }
</style>
</head>
<body>

<?php require 'partials/navbar.php'; ?>

<?php if (isset($_GET['status'])): ?>
    <div class="flash-wrap">
        <div class="flash sukses">
            <?php if ($_GET['status'] === 'perkara_sukses'): ?>
                Perkara baru berhasil ditambahkan.
            <?php elseif ($_GET['status'] === 'upload_sukses'): ?>
                <?php $jml = isset($_GET['jumlah']) ? (int) $_GET['jumlah'] : 1; ?>
                <?= $jml > 1 ? "$jml dokumen berhasil diupload." : "Dokumen berhasil diupload." ?>
            <?php elseif ($_GET['status'] === 'hapus_sukses'): ?>
                Data berhasil dihapus.
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<section class="hero">
    <img class="hero-watermark" src="assets/logo_pidsus.png" alt="">
    <div class="hero-content">
        <span class="hero-kicker">Arsip Digital Resmi</span>
        <h2 class="serif">Monitoring Proses Penanganan Perkara<br>Bidang Tindak Pidana Khusus</h2>
        <p class="hero-instansi serif">Kejaksaan Negeri Kota Kediri</p>
        <p>Sistem arsip terpadu Bidang Tindak Pidana Khusus — memastikan setiap dokumen perkara tersimpan aman, tertata rapi, dan siap diakses tanpa perlu dicetak.</p>
    </div>
</section>

<div class="stats">
    <div class="stat-card">
        <div class="stat-number serif"><?= $total_perkara ?></div>
        <div class="stat-label">Total Perkara</div>
    </div>
    <div class="stat-card">
        <div class="stat-number serif"><?= $total_dokumen ?></div>
        <div class="stat-label">Dokumen Tersimpan</div>
    </div>
    <div class="stat-card">
        <div class="stat-number serif"><?= $total_selesai ?></div>
        <div class="stat-label">Perkara Selesai</div>
    </div>
</div>

<div class="tahap-section">
    <h3>Perkara berdasarkan tahap</h3>
    <div class="tahap-grid">
        <?php foreach ($daftar_tahap as $t): ?>
            <a class="tahap-card" href="perkara_tahap.php?tahap=<?= urlencode($t) ?>">
                <div class="tahap-count serif"><?= $jumlah_per_tahap[$t] ?></div>
                <div class="tahap-name"><?= htmlspecialchars($t) ?></div>
            </a>
        <?php endforeach; ?>
    </div>
</div>

<div class="actions">
    <a class="action-card" href="daftar_dokumen.php">
        <span class="action-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M3 7C3 5.9 3.9 5 5 5H9L11 7H19C20.1 7 21 7.9 21 9V17C21 18.1 20.1 19 19 19H5C3.9 19 3 18.1 3 17V7Z" stroke="#fff" stroke-width="1.6" stroke-linejoin="round"/></svg>
        </span>
        <span>
            <p class="action-title">Lihat daftar berkas</p>
            <p class="action-sub">Telusuri seluruh perkara dan dokumen terkait</p>
        </span>
    </a>
    <a class="action-card secondary" href="upload.php">
        <span class="action-icon">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none"><path d="M12 4V16M12 4L7 9M12 4L17 9" stroke="#5c1524" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round"/><path d="M4 16V18C4 19.1 4.9 20 6 20H18C19.1 20 20 19.1 20 18V16" stroke="#5c1524" stroke-width="1.6" stroke-linecap="round"/></svg>
        </span>
        <span>
            <p class="action-title">Unggah dokumen baru</p>
            <p class="action-sub">Tambahkan berkas ke perkara yang sesuai</p>
        </span>
    </a>
</div>

<footer class="pidsus-footer">
    <strong>Kejaksaan Negeri Kota Kediri</strong> — Bidang Tindak Pidana Khusus<br>
    Sistem Arsip Berkas Digital
</footer>

</body>
<script>
    // Bersihkan parameter ?status=... dari URL setelah notifikasi ditampilkan,
    // biar kalau halaman di-refresh, notifikasinya tidak muncul lagi terus-terusan
    if (window.location.search.includes('status=')) {
        const urlBersih = window.location.pathname;
        window.history.replaceState({}, document.title, urlBersih);
    }
</script>
</html>