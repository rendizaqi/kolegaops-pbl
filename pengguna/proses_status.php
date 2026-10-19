<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);

if ($id === (int) $_SESSION['user_id']) {
    flash('error', 'Akun Anda sendiri tidak bisa dinonaktifkan.');
} else {
    $stmt = $pdo->prepare("UPDATE users SET aktif = 1 - aktif WHERE id = :id");
    $stmt->execute(['id' => $id]);
    flash('success', 'Status akun diperbarui.');
}

header('Location: list.php');
exit;
