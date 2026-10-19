<?php
require __DIR__ . '/includes/functions.php';
$u = require_login();
$db = pdo();

$st = $db->prepare('SELECT * FROM transaksi WHERE id = ?');
$st->execute([(int) ($_GET['id'] ?? 0)]);
$t = $st->fetch();
if (!$t) {
    http_response_code(404);
    exit('<p style="font:16px system-ui;padding:24px">Struk tidak ditemukan. <a href="riwayat.php">Kembali</a></p>');
}
$st = $db->prepare('SELECT * FROM transaksi_item WHERE transaksi_id = ? ORDER BY id');
$st->execute([$t['id']]);
$items = $st->fetchAll();
$open = $t['status'] === 'open';
$batal = $t['status'] === 'batal';
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Struk <?= e($t['kode']) ?> · KolegaOps</title>
<link rel="stylesheet" href="styles.css">
</head>
<body class="struk-body">
<div class="struk-actions">
  <button type="button" class="btn btn-primary" onclick="window.print()">Cetak struk</button>
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
    <div><dt>No.</dt><dd><?= e($t['kode']) ?></dd></div>
    <div><dt>Tanggal</dt><dd><?= e(date('d/m/Y', strtotime($t['tanggal']))) ?> <?= e($t['waktu']) ?></dd></div>
    <div><dt>Kasir</dt><dd><?= e($t['kasir']) ?> (<?= e($t['shift']) ?>)</dd></div>
    <?php if ($t['meja'] !== ''): ?><div><dt>Meja</dt><dd><?= e($t['meja']) ?></dd></div><?php endif; ?>
  </dl>

  <div class="struk-line"></div>
  <?php foreach ($items as $it): ?>
    <div class="struk-item">
      <div><?= e($it['nama']) ?></div>
      <div class="struk-row">
        <span><?= (int) $it['qty'] ?> x <?= e(number_format((int) $it['harga'], 0, ',', '.')) ?></span>
        <span><?= e(number_format((int) $it['qty'] * (int) $it['harga'], 0, ',', '.')) ?></span>
      </div>
    </div>
  <?php endforeach; ?>
  <div class="struk-line"></div>

  <div class="struk-row struk-total"><span>TOTAL</span><span><?= e(rp($t['total'])) ?></span></div>
  <?php if (!$open): ?>
    <div class="struk-row"><span>Bayar (<?= $t['metode'] === 'qris' ? 'QRIS' : 'Tunai' ?>)</span><span><?= e(rp($t['bayar'])) ?></span></div>
    <?php if ($t['metode'] !== 'qris'): ?>
      <div class="struk-row"><span>Kembalian</span><span><?= e(rp($t['kembalian'])) ?></span></div>
    <?php endif; ?>
  <?php endif; ?>

  <div class="struk-line"></div>
  <footer class="struk-foot">
    Terima kasih sudah mampir.<br>Sampai jumpa lagi di Warkop Kolega!
  </footer>
</article>
</body>
</html>
