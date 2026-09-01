<?php
require 'config.php';

$link_unik = isset($_GET['link']) ? $_GET['link'] : '';

if (empty($link_unik)) {
    die("Link tidak valid.");
}

$stmt = mysqli_prepare($conn, "
    SELECT d.nama_file, d.path_file, d.jenis_dokumen, d.tanggal_upload,
           p.nomor_perkara, p.judul_perkara
    FROM dokumen d
    JOIN perkara p ON d.perkara_id = p.id
    WHERE d.link_unik = ?
");
mysqli_stmt_bind_param($stmt, "s", $link_unik);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$dokumen = mysqli_fetch_assoc($result);

if (!$dokumen) {
    die("Dokumen tidak ditemukan. Link mungkin salah atau sudah tidak berlaku.");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($dokumen['jenis_dokumen']) ?> - <?= htmlspecialchars($dokumen['nomor_perkara']) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root{
            --maroon-950:#3c0f19;
            --gold-500:#d9a520;
        }
        body { font-family:'Inter',Arial,sans-serif; margin: 0; padding: 0; }
        .letterhead-band{
            height:5px;
            background:repeating-linear-gradient(90deg, var(--gold-500) 0 18px, var(--maroon-950) 18px 36px);
        }
        .header {
            background: var(--maroon-950); color: white; padding: 14px 20px;
            display:flex; align-items:center; gap:12px;
        }
        .header img{ height:32px; }
        .header h2 { margin: 0 0 3px 0; font-size: 16px; }
        .header p { margin: 0; font-size: 12.5px; opacity: 0.8; }
        .viewer-container {
            width: 100%; height: calc(100vh - 65px);
        }
        iframe { width: 100%; height: 100%; border: none; }
    </style>
</head>
<body>

<div class="letterhead-band"></div>
<div class="header">
    <img src="assets/logo_pidsus.png" alt="Logo Pidsus">
    <div>
        <h2><?= htmlspecialchars($dokumen['jenis_dokumen']) ?></h2>
        <p>
            <?= htmlspecialchars($dokumen['nomor_perkara']) ?> - <?= htmlspecialchars($dokumen['judul_perkara']) ?>
            &nbsp;|&nbsp; Diupload: <?= htmlspecialchars($dokumen['tanggal_upload']) ?>
        </p>
    </div>
</div>

<div class="viewer-container">
    <iframe src="<?= htmlspecialchars($dokumen['path_file']) ?>"></iframe>
</div>

</body>
</html>