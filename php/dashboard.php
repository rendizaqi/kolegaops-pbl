<?php
require __DIR__ . '/includes/functions.php';
$db = pdo();
$today = date('Y-m-d');

// Ringkasan hari ini.
$st = $db->prepare("SELECT COALESCE(SUM(total),0) FROM transaksi WHERE tanggal=? AND status='lunas'");
$st->execute([$today]);
$penjualan = (int) $st->fetchColumn();

$st = $db->prepare("SELECT COUNT(*) FROM transaksi WHERE tanggal=?");
$st->execute([$today]);
$jumlah = (int) $st->fetchColumn();

$st = $db->prepare("SELECT COUNT(*) FROM transaksi WHERE tanggal=? AND status='lunas'");
$st->execute([$today]);
$jumlahLunas = (int) $st->fetchColumn();
$rata = $jumlahLunas ? $penjualan / $jumlahLunas : 0;

// Stok kritis.
$bahan = $db->query("SELECT * FROM bahan ORDER BY id")->fetchAll();
$kritis = 0;
foreach ($bahan as $b) {
    if (stok_status((float) $b['qty'], (float) $b['minimum']) === 'kritis') {
        $kritis++;
    }
}

// Grafik 7 hari + transaksi terbaru.
$week = $db->query("SELECT * FROM ringkasan_harian ORDER BY tanggal")->fetchAll();
$weekTotal = array_sum(array_column($week, 'total'));
$maxWeek = max(1, ...array_column($week, 'total'));
$recent = $db->query("SELECT * FROM transaksi ORDER BY id DESC LIMIT 8")->fetchAll();

$page = 'dashboard';
$title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Dashboard</h1>
    <span class="meta">Ringkasan hari ini · <?= e(date('d M Y')) ?></span>
  </div>
  <span class="meta">Diperbarui <?= e(date('H:i')) ?></span>
</div>

<div class="stats">
  <div class="stat stat-primary">
    <span class="stat-label">Total penjualan</span>
    <span class="stat-value"><?= e(rp($penjualan)) ?></span>
    <span class="stat-note">Transaksi lunas hari ini</span>
  </div>
  <div class="stat">
    <span class="stat-label">Jumlah transaksi</span>
    <span class="stat-value"><?= e((string) $jumlah) ?></span>
    <span class="stat-note">Rata-rata <?= e(rp($rata)) ?></span>
  </div>
  <div class="stat stat-danger">
    <span class="stat-label">Stok kritis</span>
    <span class="stat-value"><?= e((string) $kritis) ?> bahan</span>
    <a href="stok.php">Lihat stok &rarr;</a>
  </div>
</div>

<div class="dash-grid">
  <section class="panel dash-card">
    <div class="dash-card-head">
      <h2 class="dash-card-title">Pendapatan 7 hari terakhir</h2>
      <span class="meta">Total <?= e(rp($weekTotal)) ?></span>
    </div>
    <div class="chart">
      <?php foreach ($week as $i => $w): ?>
        <div class="chart-col" title="<?= e($w['hari'] . ': ' . rp($w['total'])) ?>">
          <span class="chart-value"><?= e(number_format($w['total'] / 1e6, 1, ',', '.')) ?>jt</span>
          <div class="chart-bar-area">
            <div class="chart-bar<?= $i === count($week) - 1 ? ' is-today' : '' ?>"
                 style="height: <?= (int) round($w['total'] / $maxWeek * 100) ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="chart-days">
      <?php foreach ($week as $w): ?>
        <span><?= e($w['hari']) ?></span>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel dash-card">
    <div class="dash-card-head">
      <h2 class="dash-card-title">Transaksi terbaru</h2>
      <a href="laporan.php" class="strong" style="font-size:12px">Semua &rarr;</a>
    </div>
    <div class="trow thead dash-thead" style="grid-template-columns:0.8fr 1fr 1fr 0.9fr">
      <span>Waktu</span><span>Kasir</span><span>Total</span><span>Status</span>
    </div>
    <div class="dash-rows">
      <?php foreach ($recent as $t): ?>
        <div class="trow dash-row" style="grid-template-columns:0.8fr 1fr 1fr 0.9fr">
          <span class="strong"><?= e($t['waktu']) ?></span>
          <span><?= e($t['kasir']) ?></span>
          <span class="strong"><?= e(rp($t['total'])) ?></span>
          <span>
            <?php if ($t['status'] === 'lunas'): ?>
              <span class="badge badge-ok badge-xs">Lunas</span>
            <?php else: ?>
              <span class="badge badge-bad badge-xs">Batal</span>
            <?php endif; ?>
          </span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
