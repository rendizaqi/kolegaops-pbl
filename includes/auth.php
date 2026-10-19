<?php
// Guard clause: di-include di baris paling atas setiap halaman yang
// membutuhkan login (sebelum header.php mengeluarkan output apa pun),
// agar header('Location: ...') masih bisa dipanggil.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    // index.php ada di root proyek, halaman lain satu folder di bawahnya.
    $awalan = realpath(dirname($_SERVER['SCRIPT_FILENAME'])) === realpath(__DIR__ . '/..') ? '' : '../';
    header('Location: ' . $awalan . 'auth/login.php');
    exit;
}

$loginPemilik = ($_SESSION['role'] ?? '') === 'owner';
