<?php
// Aktifkan / nonaktifkan menu (menu nonaktif tidak tampil di kasir).
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $stmt = $pdo->prepare("UPDATE menu SET aktif = 1 - aktif WHERE id = :id");
    $stmt->execute(['id' => (int) ($_POST['id'] ?? 0)]);
}

header('Location: list.php');
exit;
