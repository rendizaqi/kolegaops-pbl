<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

$stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username AND aktif = 1");
$stmt->execute(['username' => $username]);
$user = $stmt->fetch();

if ($user && password_verify($password, $user['password'])) {
    // Regenerasi session ID setelah login berhasil untuk mencegah session fixation.
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['id'];
    $_SESSION['nama'] = $user['nama'];
    $_SESSION['role'] = $user['role'];
    // Keranjang kasir tidak ikut berpindah ke orang lain.
    unset($_SESSION['keranjang'], $_SESSION['bayar'], $_SESSION['metode'], $_SESSION['meja']);

    // Pemilik ke dashboard, pegawai langsung ke kasir.
    header('Location: ' . ($user['role'] === 'owner' ? '../index.php' : '../kasir/index.php'));
    exit;
}

flash('error', 'Akun atau kata sandi salah.');
header('Location: login.php');
exit;
