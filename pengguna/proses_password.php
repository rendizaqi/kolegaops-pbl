<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$password = $_POST['password'] ?? '';

if (strlen($password) < 6) {
    flash('error', 'Kata sandi baru minimal 6 karakter.');
} else {
    $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE id = :id");
    $stmt->execute(['password' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
    flash('success', 'Kata sandi diganti.');
}

header('Location: list.php');
exit;
