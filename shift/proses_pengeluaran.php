<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/shift.php';

csrf_verify();

$shift = shift_aktif($pdo);
if (!$shift || ((int) $shift['kasir_id'] !== (int) $_SESSION['user_id'] && !$loginPemilik)) {
    flash('error', 'Hanya kasir shift ini atau pemilik yang bisa mengubah pengeluaran.');
    header('Location: index.php');
    exit;
}

$aksi = $_POST['aksi'] ?? '';

if ($aksi === 'tambah') {
    $keterangan = trim($_POST['keterangan'] ?? '');
    $nominal = ke_angka($_POST['nominal'] ?? '0');
    if ($keterangan === '' || $nominal <= 0) {
        flash('error', 'Isi keterangan dan nominal pengeluaran.');
    } else {
        $stmt = $pdo->prepare("INSERT INTO pengeluaran (shift_id, keterangan, nominal) VALUES (:shift_id, :keterangan, :nominal)");
        $stmt->execute(['shift_id' => $shift['id'], 'keterangan' => mb_substr($keterangan, 0, 100), 'nominal' => $nominal]);
        flash('success', 'Pengeluaran "' . $keterangan . '" ' . rp($nominal) . ' dicatat.');
    }
} elseif ($aksi === 'hapus') {
    $stmt = $pdo->prepare("DELETE FROM pengeluaran WHERE id = :id AND shift_id = :shift_id");
    $stmt->execute(['id' => (int) ($_POST['id'] ?? 0), 'shift_id' => $shift['id']]);
    flash('success', 'Pengeluaran dihapus.');
}

header('Location: index.php');
exit;
