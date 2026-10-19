<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/shift.php';

csrf_verify();

$nama = $_POST['nama'] ?? '';
$modal = ke_angka($_POST['modal'] ?? '0');

$berjalan = shift_aktif($pdo);
if ($berjalan) {
    flash('error', 'Masih ada shift ' . $berjalan['nama'] . ' yang berjalan. Tutup dulu sebelum membuka shift baru.');
    header('Location: index.php');
    exit;
}
if (!in_array($nama, daftar_shift(), true)) {
    flash('error', 'Pilih shift Pagi, Siang, atau Malam.');
    header('Location: index.php');
    exit;
}

// Kasir shift = akun yang sedang login.
$stmt = $pdo->prepare("INSERT INTO shift (nama, kasir_id, modal_awal, status) VALUES (:nama, :kasir_id, :modal, 'buka')");
$stmt->execute(['nama' => $nama, 'kasir_id' => $_SESSION['user_id'], 'modal' => $modal]);

flash('success', 'Shift ' . $nama . ' dibuka dengan kasir ' . $_SESSION['nama'] . '. Modal awal ' . rp($modal) . '.');
header('Location: ../kasir/index.php');
exit;
