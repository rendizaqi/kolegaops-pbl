<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$nama = trim($_POST['nama'] ?? '');
$satuan = trim($_POST['satuan'] ?? '');
$qty = (float) str_replace(',', '.', trim($_POST['qty'] ?? '0'));
$minimum = (float) str_replace(',', '.', trim($_POST['minimum'] ?? '0'));

if ($nama === '' || $satuan === '' || $qty < 0 || $minimum < 0) {
    flash('error', 'Nama dan satuan wajib diisi, stok tidak boleh negatif.');
    header('Location: tambah.php');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO bahan (nama, qty, satuan, minimum) VALUES (:nama, :qty, :satuan, :minimum)");
$stmt->execute(['nama' => $nama, 'qty' => $qty, 'satuan' => $satuan, 'minimum' => $minimum]);

flash('success', 'Bahan "' . $nama . '" ditambahkan.');
header('Location: list.php');
exit;
