<?php
require 'config.php';
require 'partials/auth_check.php';

$pesan = '';
$jenis_pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $password_lama = $_POST['password_lama'];
    $password_baru = $_POST['password_baru'];
    $konfirmasi = $_POST['konfirmasi_password'];

    $stmt = mysqli_prepare($conn, "SELECT id, password FROM akun WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $_SESSION['username']);
    mysqli_stmt_execute($stmt);
    $akun = mysqli_stmt_get_result($stmt)->fetch_assoc();

    if (!$akun || !password_verify($password_lama, $akun['password'])) {
        $pesan = 'Password lama yang kamu masukkan salah.';
        $jenis_pesan = 'gagal';
    } elseif (strlen($password_baru) < 8) {
        $pesan = 'Password baru minimal 8 karakter.';
        $jenis_pesan = 'gagal';
    } elseif ($password_baru !== $konfirmasi) {
        $pesan = 'Konfirmasi password baru tidak cocok.';
        $jenis_pesan = 'gagal';
    } else {
        $hash_baru = password_hash($password_baru, PASSWORD_DEFAULT);
        $stmt_update = mysqli_prepare($conn, "UPDATE akun SET password = ? WHERE id = ?");
        mysqli_stmt_bind_param($stmt_update, "si", $hash_baru, $akun['id']);
        mysqli_stmt_execute($stmt_update);
        $pesan = 'Password berhasil diganti. Gunakan password baru saat login berikutnya.';
        $jenis_pesan = 'sukses';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Ganti Password — Arsip Pidsus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<?php require 'partials/navbar.php'; ?>

<div class="page-shell">
    <div class="page-head">
        <div>
            <h2 class="serif">Ganti Password</h2>
            <p>Ubah password akun bersama yang digunakan untuk masuk ke sistem</p>
        </div>
    </div>

    <?php if ($pesan): ?>
        <div class="flash <?= $jenis_pesan ?>"><?= htmlspecialchars($pesan) ?></div>
    <?php endif; ?>

    <div class="card">
        <form method="POST" action="ganti_password.php">
            <label for="password_lama">Password Lama</label>
            <input type="password" name="password_lama" id="password_lama" required>

            <label for="password_baru">Password Baru</label>
            <input type="password" name="password_baru" id="password_baru" required minlength="8">
            <p class="field-hint">Minimal 8 karakter</p>

            <label for="konfirmasi_password">Konfirmasi Password Baru</label>
            <input type="password" name="konfirmasi_password" id="konfirmasi_password" required minlength="8">

            <div style="margin-top:24px;">
                <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
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