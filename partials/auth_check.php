<?php
// Dipasang di baris atas setiap halaman yang wajib login.
// config.php harus sudah di-require SEBELUM file ini (butuh session_start()).

if (!isset($_SESSION['sudah_login']) || $_SESSION['sudah_login'] !== true) {
    header("Location: login.php");
    exit;
}
?>