<?php
require __DIR__ . '/includes/functions.php';
$u = require_owner();
$db = pdo();
$today = date('Y-m-d');

$st = $db->prepare("SELECT COUNT(*) AS n, COALESCE(SUM(total),0) AS t, COALESCE(SUM(hpp),0) AS h FROM transaksi WHERE tanggal = ? AND status = 'lunas'");
$st->execute([$today]);
$hari = $st->fetch();
$penjualan = (int) $hari['t'];
$jumlah = (int) $hari['n'];
$laba = $penjualan - (int) $hari['h'];
$rata = $jumlah ? $penjualan / $jumlah : 0;

$st = $db->prepare('SELECT COALESCE(SUM(p.nominal),0) FROM pengeluaran p JOIN shift s ON s.id = p.shift_id WHERE s.tanggal = ?');
$st->execute([$today]);
$keluar = (int) $st->fetchColumn();

$menuRendah = $db->query('SELECT nama, stok FROM menu WHERE aktif = 1 AND stok <= ' . STOK_MENU_RENDAH . ' ORDER BY stok, nama')->fetchAll();
$kritis = 0;
foreach ($db->query('SELECT qty, minimum FROM bahan')->fetchAll() as $b) {
    if (stok_status((float) $b['qty'], (float) $b['minimum']) === 'kritis') {
        $kritis++;
    }
}

// Grafik: 6 hari sebelumnya dari ringkasan + hari ini dari transaksi.
$week = [];
$st = $db->prepare('SELECT * FROM ringkasan_harian WHERE tanggal < ? ORDER BY tanggal DESC LIMIT 6');
$st->execute([$today]);
foreach (array_reverse($st->fetchAll()) as $r) {
    $week[] = [$r['hari'], (int) $r['total']];
}
$week[] = [hari_singkat($today), $penjualan];
$weekTotal = array_sum(array_column($week, 1));
$maxWeek = max(1, ...array_column($week, 1));

$recent = $db->query("SELECT * FROM transaksi WHERE status <> 'open' ORDER BY id DESC LIMIT 8")->fetchAll();
$shift = current_shift();

$page = 'dashboard';
$title = 'Dashboard';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Dashboard</h1>
    <span class="meta">Ringkasan hari ini · <?= e(tgl_id()) ?></span>
  </div>
  <span class="meta">Diperbarui <?= e(date('H:i')) ?></span>
</div>

<div class="stats stats4">
  <div class="stat stat-primary">
    <span class="stat-label">Total penjualan</span>
    <span class="stat-value"><?= e(rp($penjualan)) ?></span>
    <span class="stat-note"><?= $jumlah ?> transaksi · rata-rata <?= e(rp($rata)) ?></span>
  </div>
  <div class="stat">
    <span class="stat-label">Laba kotor</span>
    <span class="stat-value"><?= e(rp($laba)) ?></span>
    <span class="stat-note">Penjualan − HPP · pengeluaran <?= e(rp($keluar)) ?></span>
  </div>
  <div class="stat<?= $menuRendah ? ' stat-warn' : '' ?>">
    <span class="stat-label">Stok menu menipis</span>
    <span class="stat-value"><?= count($menuRendah) ?> menu</span>
    <a href="menu.php" style="font-size:12px;font-weight:700">Isi ulang stok →</a>
  </div>
  <div class="stat stat-danger">
    <span class="stat-label">Bahan baku kritis</span>
    <span class="stat-value"><?= $kritis ?> bahan</span>
    <a href="stok.php">Lihat stok →</a>
  </div>
</div>

<div class="dash-grid">
  <section class="panel dash-card">
    <div class="dash-card-head">
      <h2 class="dash-card-title">Pendapatan 7 hari terakhir</h2>
      <span class="meta">Total <?= e(rp($weekTotal)) ?></span>
    </div>
    <div class="chart">
      <?php foreach ($week as $i => [$hr, $tot]): ?>
        <div class="chart-col" title="<?= e($hr . ': ' . rp($tot)) ?>">
          <span class="chart-value"><?= e(number_format($tot / 1e6, 1, ',', '.')) ?>jt</span>
          <div class="chart-bar-area">
            <div class="chart-bar<?= $i === count($week) - 1 ? ' is-today' : '' ?>" style="height:<?= (int) round($tot / $maxWeek * 100) ?>%"></div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="chart-days"><?php foreach ($week as [$hr]): ?><span><?= e($hr) ?></span><?php endforeach; ?></div>
  </section>

  <section class="panel dash-card">
    <div class="dash-card-head">
      <h2 class="dash-card-title">Transaksi terbaru</h2>
      <a href="riwayat.php" class="strong" style="font-size:12px">Semua →</a>
    </div>
    <div class="trow thead dash-thead" style="grid-template-columns:.8fr 1fr 1fr .9fr"><span>Waktu</span><span>Kasir</span><span>Total</span><span>Status</span></div>
    <div class="dash-rows">
      <?php foreach ($recent as $t): ?>
        <div class="trow dash-row" style="grid-template-columns:.8fr 1fr 1fr .9fr">
          <span class="strong"><?= e($t['waktu']) ?></span><span><?= e($t['kasir']) ?></span><span class="strong"><?= e(rp($t['total'])) ?></span>
          <span><?= $t['status'] === 'lunas' ? '<span class="badge badge-ok badge-xs">Lunas</span>' : '<span class="badge badge-bad badge-xs">Batal</span>' ?></span>
        </div>
      <?php endforeach; ?>
      <?php if (!$recent): ?><div class="empty">Belum ada transaksi.</div><?php endif; ?>
    </div>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
