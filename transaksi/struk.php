<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';

$stmt = $pdo->prepare(
    "SELECT t.*, s.nama AS shift, u.nama AS kasir
     FROM transaksi t
     JOIN shift s ON s.id = t.shift_id
     JOIN users u ON u.id = s.kasir_id
     WHERE t.id = :id"
);
$stmt->execute(['id' => (int) ($_GET['id'] ?? 0)]);
$t = $stmt->fetch();

if (!$t) {
    http_response_code(404);
    die('Struk tidak ditemukan. <a href="riwayat.php">Kembali</a>');
}

$stmt = $pdo->prepare("SELECT * FROM transaksi_item WHERE transaksi_id = :id ORDER BY id");
$stmt->execute(['id' => $t['id']]);
$daftarItem = $stmt->fetchAll();

$open = $t['status'] === 'open';
$batal = $t['status'] === 'batal';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk <?php echo e($t['kode']); ?> | KolegaOps</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="struk-body">
    <div class="struk-actions">
        <button type="button" id="btn-cetak" class="btn btn-primary">Cetak struk</button>
        <a class="btn btn-plain" href="riwayat.php">Kembali</a>
    </div>

    <article class="struk">
        <header class="struk-head">
            <div class="struk-brand">WARKOP KOLEGA</div>
            <div>Struk pembelian</div>
        </header>
        <?php if ($open): ?><div class="struk-flag">BELUM LUNAS (open bill)</div><?php endif; ?>
        <?php if ($batal): ?><div class="struk-flag">DIBATALKAN</div><?php endif; ?>

        <dl class="struk-meta">
            <div><dt>No.</dt><dd><?php echo e($t['kode']); ?></dd></div>
            <div><dt>Tanggal</dt><dd><?php echo date('d/m/Y H:i', strtotime($t['dibuat'])); ?></dd></div>
            <div><dt>Kasir</dt><dd><?php echo e($t['kasir']); ?> (<?php echo e($t['shift']); ?>)</dd></div>
            <?php if ($t['meja'] !== ''): ?><div><dt>Meja</dt><dd><?php echo e($t['meja']); ?></dd></div><?php endif; ?>
        </dl>

        <div class="struk-line"></div>
        <?php foreach ($daftarItem as $item): ?>
        <div class="struk-item">
            <div><?php echo e($item['nama']); ?></div>
            <div class="struk-row">
                <span><?php echo (int) $item['qty']; ?> x <?php echo number_format((int) $item['harga'], 0, ',', '.'); ?></span>
                <span><?php echo number_format((int) $item['qty'] * (int) $item['harga'], 0, ',', '.'); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="struk-line"></div>

        <div class="struk-row struk-total"><span>TOTAL</span><span><?php echo e(rp($t['total'])); ?></span></div>
        <?php if (!$open): ?>
        <div class="struk-row"><span>Bayar (<?php echo $t['metode'] === 'qris' ? 'QRIS' : 'Tunai'; ?>)</span><span><?php echo e(rp($t['bayar'])); ?></span></div>
            <?php if ($t['metode'] !== 'qris'): ?>
            <div class="struk-row"><span>Kembalian</span><span><?php echo e(rp($t['kembalian'])); ?></span></div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="struk-line"></div>
        <footer class="struk-foot">
            Terima kasih sudah mampir.<br>Sampai jumpa lagi di Warkop Kolega!
        </footer>
    </article>
    <script src="../assets/js/app.js"></script>
</body>
</html>
