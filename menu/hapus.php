<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

// Sengaja hanya menerima POST (bukan GET) agar penghapusan tidak bisa
// dipicu tanpa sengaja lewat link/preview crawler.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: list.php');
    exit;
}

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
if ($id > 0) {
    // Riwayat transaksi lama tetap aman: nama & harga sudah disalin ke transaksi_item.
    $stmt = $pdo->prepare("DELETE FROM menu WHERE id = :id");
    $stmt->execute(['id' => $id]);
    flash('success', 'Menu berhasil dihapus.');
}

header('Location: list.php');
exit;
