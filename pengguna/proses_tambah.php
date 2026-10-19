<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$nama = trim($_POST['nama'] ?? '');
$username = strtolower(trim($_POST['username'] ?? ''));
$password = $_POST['password'] ?? '';
$role = ($_POST['role'] ?? '') === 'owner' ? 'owner' : 'pegawai';

$errors = [];
if ($nama === '') {
    $errors[] = "Nama wajib diisi.";
}
if (!preg_match('/^[a-z0-9_.]{3,30}$/', $username)) {
    $errors[] = "Username 3-30 karakter (huruf kecil, angka, titik, garis bawah).";
}
if (strlen($password) < 6) {
    $errors[] = "Kata sandi minimal 6 karakter.";
}

if (empty($errors)) {
    $cek = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = :username");
    $cek->execute(['username' => $username]);
    if ($cek->fetchColumn() > 0) {
        $errors[] = "Username sudah dipakai.";
    }
}

if (!empty($errors)) {
    flash('error', implode(' ', $errors));
    header('Location: tambah.php');
    exit;
}

// Kata sandi disimpan sebagai hash, tidak pernah sebagai teks asli.
$stmt = $pdo->prepare(
    "INSERT INTO users (nama, username, password, role) VALUES (:nama, :username, :password, :role)"
);
$stmt->execute([
    'nama' => $nama,
    'username' => $username,
    'password' => password_hash($password, PASSWORD_DEFAULT),
    'role' => $role,
]);

flash('success', 'Akun ' . $nama . ' dibuat.');
header('Location: list.php');
exit;
