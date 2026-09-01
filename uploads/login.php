<?php
require 'config.php';

// Kalau sudah login, langsung lempar ke beranda, tidak perlu login lagi
if (isset($_SESSION['sudah_login']) && $_SESSION['sudah_login'] === true) {
    header("Location: index.php");
    exit;
}

$pesan_error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    $stmt = mysqli_prepare($conn, "SELECT id, username, password FROM akun WHERE username = ?");
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $akun = mysqli_fetch_assoc($result);

    if ($akun && password_verify($password, $akun['password'])) {
        $_SESSION['sudah_login'] = true;
        $_SESSION['username'] = $akun['username'];
        header("Location: index.php");
        exit;
    } else {
        $pesan_error = 'Username atau password salah.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Masuk — Arsip Pidsus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="assets/style.css">
<style>
    .login-wrap{
        min-height:calc(100vh - 6px);
        display:flex;
        align-items:center;
        justify-content:center;
        padding:24px;
    }
    .login-card{
        background:var(--paper);
        border:1px solid var(--line);
        border-top:4px solid var(--gold-500);
        border-radius:4px;
        padding:36px 32px;
        width:100%;
        max-width:380px;
        text-align:center;
    }
    .login-card img{ height:64px; margin-bottom:14px; }
    .login-card h2{ font-family:'Source Serif 4', serif; color:var(--maroon-950); font-size:20px; margin:0 0 4px; }
    .login-card p.sub{ color:var(--muted); font-size:13px; margin:0 0 22px; }
    .login-card form{ text-align:left; }
    .login-card button{ width:100%; margin-top:22px; justify-content:center; }
</style>
</head>
<body>

<div class="letterhead-band"></div>

<div class="login-wrap">
    <div class="login-card">
        <img src="assets/logo_pidsus.png" alt="Logo Pidsus">
        <h2>Sistem Arsip Berkas Digital</h2>
        <p class="sub">Bidang Tindak Pidana Khusus — Kejaksaan Negeri Kota Kediri</p>

        <?php if ($pesan_error): ?>
            <div class="flash gagal"><?= htmlspecialchars($pesan_error) ?></div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username">Username</label>
            <input type="text" name="username" id="username" required autofocus>

            <label for="password">Password</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn btn-primary">Masuk</button>
        </form>
    </div>
</div>

</body>
</html>