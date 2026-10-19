<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

csrf_verify();

$nama = trim($_POST['nama'] ?? '');
$kategori = trim($_POST['kategori'] ?? '');
$hpp = ke_angka($_POST['hpp'] ?? '0');
$harga = ke_angka($_POST['harga'] ?? '0');
$stok = $_POST['stok'] ?? '';
$aktif = isset($_POST['aktif']) ? 1 : 0;

// Validasi server-side — wajib ada meski form sudah divalidasi di browser.
$errors = [];
if ($nama === '') {
    $errors[] = "Nama produk wajib diisi.";
}
if (!in_array($kategori, daftar_kategori(), true)) {
    $errors[] = "Kategori tidak valid.";
}
if ($harga <= 0) {
    $errors[] = "Harga jual harus lebih dari 0.";
}
if (!is_numeric($stok) || $stok < 0) {
    $errors[] = "Stok tidak boleh negatif.";
}

if (!empty($errors)) {
    flash('error', implode(' ', $errors));
    header('Location: tambah.php');
    exit;
}

$stmt = $pdo->prepare(
    "INSERT INTO menu (nama, kategori, hpp, harga, stok, aktif)
     VALUES (:nama, :kategori, :hpp, :harga, :stok, :aktif)
     RETURNING id"
);
$stmt->execute([
    'nama' => $nama,
    'kategori' => $kategori,
    'hpp' => $hpp,
    'harga' => $harga,
    'stok' => (int) $stok,
    'aktif' => $aktif,
]);

flash('success', 'Menu "' . $nama . '" berhasil ditambahkan.');
header('Location: list.php');
exit;
