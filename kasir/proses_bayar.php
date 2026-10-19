<?php
// Menyimpan transaksi lunas: hitung ulang total dari database, cek stok, kurangi stok.
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/shift.php';

csrf_verify();

$parameter = http_build_query(array_filter(['cat' => $_POST['cat'] ?? '', 'q' => $_POST['q'] ?? '']));
$kembali = 'index.php' . ($parameter ? '?' . $parameter : '');

$shift = wajib_kasir_shift($pdo, $kembali);

$keranjang = $_SESSION['keranjang'] ?? [];
if (!$keranjang) {
    flash('error', 'Pesanan masih kosong.');
    header('Location: ' . $kembali);
    exit;
}

$metode = ($_SESSION['metode'] ?? 'tunai') === 'qris' ? 'qris' : 'tunai';
$meja = $_SESSION['meja'] ?? 'Meja 1';

try {
    $pdo->beginTransaction();

    // Kunci baris menu (FOR UPDATE) agar stok tidak berubah oleh transaksi lain
    // di tengah proses ini — mencegah stok menjadi negatif.
    $total = 0;
    $hpp = 0;
    $item = [];
    $cek = $pdo->prepare("SELECT id, nama, harga, hpp, stok FROM menu WHERE id = :id FOR UPDATE");
    foreach ($keranjang as $menuId => $qty) {
        $cek->execute(['id' => $menuId]);
        $menu = $cek->fetch();
        if (!$menu || (int) $menu['stok'] < $qty) {
            throw new Exception('Stok ' . ($menu['nama'] ?? 'menu') . ' tidak cukup (sisa ' . (int) ($menu['stok'] ?? 0) . ').');
        }
        $total += (int) $menu['harga'] * $qty;
        $hpp += (int) $menu['hpp'] * $qty;
        $item[] = ['menu' => $menu, 'qty' => $qty];
    }

    // QRIS: dibayar pas sesuai total. Tunai: nominal bayar harus mencukupi.
    $bayar = $metode === 'qris' ? $total : (int) ($_SESSION['bayar'] ?? 0);
    if ($bayar < $total) {
        throw new Exception('Nominal bayar kurang dari total.');
    }

    $stmt = $pdo->prepare(
        "INSERT INTO transaksi (shift_id, metode, total, hpp, bayar, kembalian, meja, status)
         VALUES (:shift_id, :metode, :total, :hpp, :bayar, :kembalian, :meja, 'lunas')
         RETURNING id"
    );
    $stmt->execute([
        'shift_id' => $shift['id'],
        'metode' => $metode,
        'total' => $total,
        'hpp' => $hpp,
        'bayar' => $bayar,
        'kembalian' => $bayar - $total,
        'meja' => $meja,
    ]);
    $transaksiId = (int) $stmt->fetchColumn();

    $pdo->prepare("UPDATE transaksi SET kode = :kode WHERE id = :id")
        ->execute(['kode' => 'TRX-' . str_pad($transaksiId, 4, '0', STR_PAD_LEFT), 'id' => $transaksiId]);

    $simpanItem = $pdo->prepare(
        "INSERT INTO transaksi_item (transaksi_id, menu_id, nama, qty, harga, hpp)
         VALUES (:transaksi_id, :menu_id, :nama, :qty, :harga, :hpp)"
    );
    $kurangiStok = $pdo->prepare("UPDATE menu SET stok = stok - :qty WHERE id = :id");
    foreach ($item as $baris) {
        $simpanItem->execute([
            'transaksi_id' => $transaksiId,
            'menu_id' => $baris['menu']['id'],
            'nama' => $baris['menu']['nama'],
            'qty' => $baris['qty'],
            'harga' => $baris['menu']['harga'],
            'hpp' => $baris['menu']['hpp'],
        ]);
        $kurangiStok->execute(['qty' => $baris['qty'], 'id' => $baris['menu']['id']]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    flash('error', $e->getMessage());
    header('Location: ' . $kembali);
    exit;
}

$_SESSION['struk_terakhir'] = $transaksiId;
$_SESSION['keranjang'] = [];
$_SESSION['bayar'] = 0;
$_SESSION['metode'] = 'tunai';

flash('success', 'Pembayaran ' . rp($total) . ' (' . strtoupper($metode) . ') berhasil'
    . ($metode === 'tunai' ? ' · kembalian ' . rp($bayar - $total) : '') . '.');
header('Location: ' . $kembali);
exit;
