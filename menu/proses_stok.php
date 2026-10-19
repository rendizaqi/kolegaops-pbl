<?php
// Isi ulang stok menu. Boleh dilakukan semua akun (pegawai juga menambah stok
// saat habis), jadi hanya butuh login — bukan khusus pemilik.
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $id = (int) ($_POST['id'] ?? 0);
    $jumlah = ke_angka($_POST['jumlah'] ?? '0');

    if ($id > 0 && $jumlah > 0) {
        $stmt = $pdo->prepare("UPDATE menu SET stok = stok + :jumlah WHERE id = :id RETURNING nama, stok");
        $stmt->execute(['jumlah' => $jumlah, 'id' => $id]);
        $menu = $stmt->fetch();
        if ($menu) {
            flash('success', 'Stok ' . $menu['nama'] . ' bertambah ' . $jumlah . ' (sekarang ' . $menu['stok'] . ').');
        }
    } else {
        flash('error', 'Isi jumlah stok yang ditambahkan.');
    }
}

header('Location: list.php');
exit;
