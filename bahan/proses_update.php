<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$id = (int) ($_POST['id'] ?? 0);
$mode = $_POST['mode'] ?? '';
// Pemisah desimal boleh koma atau titik: "1,5" dan "1.5" sama-sama diterima.
$jumlah = (float) str_replace(',', '.', trim($_POST['jumlah'] ?? ''));

if (!in_array($mode, ['Tambah', 'Kurangi', 'Set jumlah'], true) || $jumlah < 0) {
    flash('error', 'Mode atau jumlah tidak valid.');
    header('Location: update.php?id=' . $id);
    exit;
}

$stmt = $pdo->prepare("SELECT nama, qty FROM bahan WHERE id = :id");
$stmt->execute(['id' => $id]);
$bahan = $stmt->fetch();
if (!$bahan) {
    header('Location: list.php');
    exit;
}

$baru = (float) $bahan['qty'];
if ($mode === 'Tambah') {
    $baru += $jumlah;
} elseif ($mode === 'Kurangi') {
    $baru -= $jumlah;
} else {
    $baru = $jumlah;
}

$stmt = $pdo->prepare("UPDATE bahan SET qty = :qty WHERE id = :id");
$stmt->execute(['qty' => max(0, round($baru, 2)), 'id' => $id]);

flash('success', 'Stok ' . $bahan['nama'] . ' diperbarui.');
header('Location: list.php');
exit;
