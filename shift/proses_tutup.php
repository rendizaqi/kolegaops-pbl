<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/shift.php';

csrf_verify();

$shift = shift_aktif($pdo);
if (!$shift || ((int) $shift['kasir_id'] !== (int) $_SESSION['user_id'] && !$loginPemilik)) {
    flash('error', 'Hanya kasir shift ini atau pemilik yang bisa menutup shift.');
    header('Location: index.php');
    exit;
}

$uangFisik = trim($_POST['uang_fisik'] ?? '');
$catatan = mb_substr(trim($_POST['catatan'] ?? ''), 0, 255);

// Open bill harus diselesaikan dulu supaya tidak ada pesanan yang menggantung.
$stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE shift_id = :id AND status = 'open'");
$stmt->execute(['id' => $shift['id']]);

if ((int) $stmt->fetchColumn() > 0) {
    flash('error', 'Masih ada open bill. Selesaikan atau hapus dulu sebelum menutup shift.');
    header('Location: index.php');
    exit;
}
if ($uangFisik === '') {
    flash('error', 'Isi jumlah uang tunai di laci saat ini.');
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare(
    "UPDATE shift SET status = 'tutup', tutup_at = NOW(), uang_fisik = :uang, catatan = :catatan WHERE id = :id"
);
$stmt->execute(['uang' => ke_angka($uangFisik), 'catatan' => $catatan !== '' ? $catatan : null, 'id' => $shift['id']]);

flash('success', 'Shift ' . $shift['nama'] . ' ditutup. Ini laporan shift-nya.');
header('Location: ../laporan/shift.php?id=' . (int) $shift['id']);
exit;
