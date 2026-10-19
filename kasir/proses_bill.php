<?php
// Open bill: simpan pesanan yang belum dibayar, lanjutkan lagi nanti, atau hapus.
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/shift.php';

csrf_verify();

$parameter = http_build_query(array_filter(['cat' => $_POST['cat'] ?? '', 'q' => $_POST['q'] ?? '']));
$kembali = 'index.php' . ($parameter ? '?' . $parameter : '');

$shift = wajib_kasir_shift($pdo, $kembali);

$aksi = $_POST['aksi'] ?? '';
$id = (int) ($_POST['id'] ?? 0);
$keranjang = $_SESSION['keranjang'] ?? [];

if ($aksi === 'simpan') {
    if (!$keranjang) {
        flash('error', 'Pesanan masih kosong.');
    } else {
        $meja = $_SESSION['meja'] ?? 'Meja 1';
        $pdo->beginTransaction();

        $total = 0;
        $hpp = 0;
        $harga = $pdo->prepare("SELECT id, nama, harga, hpp FROM menu WHERE id = :id");
        $item = [];
        foreach ($keranjang as $menuId => $qty) {
            $harga->execute(['id' => $menuId]);
            $menu = $harga->fetch();
            $total += (int) $menu['harga'] * $qty;
            $hpp += (int) $menu['hpp'] * $qty;
            $item[] = ['menu' => $menu, 'qty' => $qty];
        }

        $stmt = $pdo->prepare(
            "INSERT INTO transaksi (shift_id, metode, total, hpp, meja, status)
             VALUES (:shift_id, '', :total, :hpp, :meja, 'open') RETURNING id"
        );
        $stmt->execute(['shift_id' => $shift['id'], 'total' => $total, 'hpp' => $hpp, 'meja' => $meja]);
        $billId = (int) $stmt->fetchColumn();
        $kode = 'BILL-' . str_pad($billId, 4, '0', STR_PAD_LEFT);
        $pdo->prepare("UPDATE transaksi SET kode = :kode WHERE id = :id")->execute(['kode' => $kode, 'id' => $billId]);

        $simpanItem = $pdo->prepare(
            "INSERT INTO transaksi_item (transaksi_id, menu_id, nama, qty, harga, hpp)
             VALUES (:transaksi_id, :menu_id, :nama, :qty, :harga, :hpp)"
        );
        foreach ($item as $baris) {
            $simpanItem->execute([
                'transaksi_id' => $billId,
                'menu_id' => $baris['menu']['id'],
                'nama' => $baris['menu']['nama'],
                'qty' => $baris['qty'],
                'harga' => $baris['menu']['harga'],
                'hpp' => $baris['menu']['hpp'],
            ]);
        }
        $pdo->commit();

        $_SESSION['keranjang'] = [];
        $_SESSION['bayar'] = 0;
        flash('success', 'Open bill ' . $meja . ' tersimpan (' . $kode . '). Bisa dilanjutkan kapan saja.');
    }
} elseif ($aksi === 'buka') {
    // Lanjutkan open bill: isi keranjang dari bill, lalu bill lama dihapus
    // (akan disimpan lagi sebagai transaksi baru saat dibayar / disimpan ulang).
    $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE id = :id AND status = 'open'");
    $stmt->execute(['id' => $id]);
    $bill = $stmt->fetch();

    if ($keranjang) {
        flash('error', 'Selesaikan atau kosongkan pesanan yang sedang dibuat dulu.');
    } elseif (!$bill) {
        flash('error', 'Open bill tidak ditemukan.');
    } else {
        $stmt = $pdo->prepare("SELECT menu_id, qty FROM transaksi_item WHERE transaksi_id = :id");
        $stmt->execute(['id' => $id]);
        $stokMenu = array_column($pdo->query("SELECT id, aktif FROM menu")->fetchAll(), 'aktif', 'id');
        $baru = [];
        foreach ($stmt->fetchAll() as $item) {
            if (!empty($stokMenu[$item['menu_id']])) {
                $baru[(int) $item['menu_id']] = (int) $item['qty'];
            }
        }
        $_SESSION['keranjang'] = $baru;
        $_SESSION['meja'] = $bill['meja'] ?: 'Meja 1';
        $pdo->prepare("DELETE FROM transaksi WHERE id = :id")->execute(['id' => $id]);  // item ikut terhapus (CASCADE)
    }
} elseif ($aksi === 'hapus') {
    $pdo->prepare("DELETE FROM transaksi WHERE id = :id AND status = 'open'")->execute(['id' => $id]);
    flash('success', 'Open bill dihapus.');
}

header('Location: ' . $kembali);
exit;
