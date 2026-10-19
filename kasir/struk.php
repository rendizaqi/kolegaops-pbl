<?php
// Struk (tampilan cetak 80 mm). Pilih transaksi contoh lewat ?kode=TRX-0103
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/data.php';

$kode = $_GET['kode'] ?? 'TRX-0103';
$struk = null;
foreach ($transaksi as $t) {
    if ($t['kode'] === $kode) {
        $struk = $t;
    }
}
if (!$struk) {
    http_response_code(404);
    die('Struk tidak ditemukan. <a href="index.php">Kembali</a>');
}

// Uang bayar contoh: tunai dibulatkan ke atas kelipatan 10.000, QRIS pas.
$bayar = $struk['metode'] === 'qris' ? $struk['total'] : (int) (ceil($struk['total'] / 10000) * 10000);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Struk <?php echo e($struk['kode']); ?> | KolegaOps</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="struk-body">
    <div class="struk-actions">
        <button type="button" id="btn-cetak" class="btn btn-primary">Cetak struk</button>
        <button type="button" id="btn-tutup" class="btn btn-plain">Tutup</button>
    </div>

    <article class="struk">
        <header class="struk-head">
            <div class="struk-brand">WARKOP KOLEGA</div>
            <div>Struk pembelian</div>
        </header>
        <?php if ($struk['status'] === 'batal'): ?><div class="struk-flag">DIBATALKAN</div><?php endif; ?>

        <dl class="struk-meta">
            <div><dt>No.</dt><dd><?php echo e($struk['kode']); ?></dd></div>
            <div><dt>Tanggal</dt><dd><?php echo date('d/m/Y'); ?> <?php echo e($struk['waktu']); ?></dd></div>
            <div><dt>Kasir</dt><dd><?php echo e($struk['kasir']); ?> (<?php echo e($struk['shift']); ?>)</dd></div>
        </dl>

        <div class="struk-line"></div>
        <?php foreach ($struk['rincian'] as $item): ?>
        <div class="struk-item">
            <div><?php echo e($item['nama']); ?></div>
            <div class="struk-row">
                <span><?php echo $item['qty']; ?> x <?php echo number_format($item['harga'], 0, ',', '.'); ?></span>
                <span><?php echo number_format($item['qty'] * $item['harga'], 0, ',', '.'); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
        <div class="struk-line"></div>

        <div class="struk-row struk-total"><span>TOTAL</span><span><?php echo e(rp($struk['total'])); ?></span></div>
        <div class="struk-row"><span>Bayar (<?php echo $struk['metode'] === 'qris' ? 'QRIS' : 'Tunai'; ?>)</span><span><?php echo e(rp($bayar)); ?></span></div>
        <?php if ($struk['metode'] !== 'qris'): ?>
        <div class="struk-row"><span>Kembalian</span><span><?php echo e(rp($bayar - $struk['total'])); ?></span></div>
        <?php endif; ?>

        <div class="struk-line"></div>
        <footer class="struk-foot">
            Terima kasih sudah mampir.<br>Sampai jumpa lagi di Warkop Kolega!
        </footer>
    </article>
    <script src="../assets/js/app.js"></script>
</body>
</html>
