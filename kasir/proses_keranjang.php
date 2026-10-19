<?php
// Mengubah isi keranjang di session: tambah, kurang, hapus, kosongkan, metode, nominal bayar, meja.
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/shift.php';

csrf_verify();

// Setelah aksi, kembali ke kategori / pencarian yang sama.
$parameter = http_build_query(array_filter(['cat' => $_POST['cat'] ?? '', 'q' => $_POST['q'] ?? '']));
$kembali = 'index.php' . ($parameter ? '?' . $parameter : '');

wajib_kasir_shift($pdo, $kembali);

$aksi = $_POST['aksi'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$nilai = $_POST['nilai'] ?? '';
$keranjang = $_SESSION['keranjang'] ?? [];

switch ($aksi) {
    case 'tambah':
        $stmt = $pdo->prepare("SELECT nama, stok, aktif FROM menu WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $menu = $stmt->fetch();
        if (!$menu || !$menu['aktif']) {
            flash('error', 'Menu tidak tersedia.');
        } elseif (($keranjang[$id] ?? 0) + 1 > (int) $menu['stok']) {
            flash('error', 'Stok ' . $menu['nama'] . ' tidak cukup (sisa ' . (int) $menu['stok'] . ').');
        } else {
            $keranjang[$id] = ($keranjang[$id] ?? 0) + 1;
        }
        break;

    case 'kurang':
        if (isset($keranjang[$id])) {
            $keranjang[$id]--;
            if ($keranjang[$id] <= 0) {
                unset($keranjang[$id]);
            }
        }
        break;

    case 'hapus':
        unset($keranjang[$id]);
        break;

    case 'kosongkan':
        $keranjang = [];
        $_SESSION['bayar'] = 0;
        break;

    case 'metode':
        $_SESSION['metode'] = $nilai === 'qris' ? 'qris' : 'tunai';
        break;

    case 'bayar':
        $_SESSION['bayar'] = ke_angka($nilai);
        break;

    case 'pas':
        // Uang pas: nominal bayar = total pesanan saat ini (harga dari database).
        $total = 0;
        if ($keranjang) {
            $stmt = $pdo->query("SELECT id, harga FROM menu");
            $harga = array_column($stmt->fetchAll(), 'harga', 'id');
            foreach ($keranjang as $menuId => $qty) {
                $total += (int) ($harga[$menuId] ?? 0) * $qty;
            }
        }
        $_SESSION['bayar'] = $total;
        break;

    case 'meja':
        $nama = mb_substr(trim($nilai), 0, 30);
        $_SESSION['meja'] = $nama !== '' ? $nama : 'Meja 1';
        break;
}

$_SESSION['keranjang'] = $keranjang;
header('Location: ' . $kembali);
exit;
