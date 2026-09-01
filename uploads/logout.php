<?php
require 'config.php';

// Hapus semua data session, kembali ke halaman login
session_unset();
session_destroy();

header("Location: login.php");
exit;
?>