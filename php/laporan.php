<?php
require __DIR__ . '/includes/functions.php';
$db = pdo();

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$shift   = $_GET['shift'] ?? 'Semua';
$kasir   = $_GET['kasir'] ?? '';

// Daftar kasir untuk dropdown.
$kasirList = $db->query('SELECT DISTINCT kasir FROM transaksi ORDER BY kasir')->fetchAll(PDO::FETCH_COLUMN);

// Query transaksi terfilter.
$sql = 'SELECT * FROM transaksi WHERE tanggal = ?';
$args = [$tanggal];
if ($shift !== 'Semua') { $sql .= ' AND shift = ?'; $args[] = $shift; }
if ($kasir !== '')      { $sql .= ' AND kasir = ?'; $args[] = $kasir; }
$sql .= ' ORDER BY waktu';
$st = $db->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

$lunas = array_filter($rows, fn($t) => $t['status'] === 'lunas');
$sum   = array_sum(array_column($lunas, 'total'));
$avg   = count($lunas) ? $sum / count($lunas) : 0;

// Ekspor CSV (berjalan penuh di sisi server — sebelum ada output HTML).
if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="laporan-' . $tanggal . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Waktu', 'No. transaksi', 'Kasir', 'Shift', 'Total', 'Status']);
    foreach ($rows as $t) {
        fputcsv($out, [$t['waktu'], $t['kode'], $t['kasir'], $t['shift'], $t['total'], ucfirst($t['status'])]);
    }
    fclose($out);
    exit;
}

$exportUrl = 'laporan.php?' . http_build_query(['tanggal' => $tanggal, 'shift' => $shift, 'kasir' => $kasir, 'export' => 'csv']);
$shifts = ['Semua', 'Pagi', 'Siang', 'Malam'];
$cols = 'grid-template-columns:1fr 1.2fr 1fr 1fr 1fr 1fr';
$page = 'laporan';
$title = 'Laporan Transaksi';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <h1 class="page-title">Laporan Transaksi Harian</h1>
  <a class="btn btn-outline" href="<?= e($exportUrl) ?>">Ekspor CSV</a>
</div>

<form class="filters" method="get" action="laporan.php">
  <label class="field">
    <span class="label">Tanggal</span>
    <input class="input" type="date" name="tanggal" value="<?= e($tanggal) ?>" style="font-weight:600;padding-inline:10px">
  </label>
  <div class="field">
    <span class="label">Shift</span>
    <div class="seg">
      <?php foreach ($shifts as $s): ?>
        <a class="seg-opt<?= $shift === $s ? ' is-active' : '' ?>"
           href="laporan.php?<?= http_build_query(['tanggal' => $tanggal, 'shift' => $s, 'kasir' => $kasir]) ?>"><?= e($s) ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <label class="field">
    <span class="label">Kasir</span>
    <select class="input" name="kasir" style="padding-left:10px;min-width:170px">
      <option value="">Semua kasir</option>
      <?php foreach ($kasirList as $k): ?>
        <option value="<?= e($k) ?>" <?= $kasir === $k ? 'selected' : '' ?>><?= e($k) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
</form>

<div class="stats">
  <div class="stat stat-primary">
    <span class="stat-label">Total pendapatan</span>
    <span class="stat-value"><?= e(rp($sum)) ?></span>
  </div>
  <div class="stat">
    <span class="stat-label">Total transaksi</span>
    <span class="stat-value"><?= count($rows) ?></span>
  </div>
  <div class="stat">
    <span class="stat-label">Rata-rata / transaksi</span>
    <span class="stat-value"><?= e(rp($avg)) ?></span>
  </div>
</div>

<div class="panel table-wrap" style="margin-top:16px">
  <div class="trow thead" style="<?= $cols ?>">
    <span>Waktu</span><span>No. transaksi</span><span>Kasir</span><span>Shift</span><span>Total</span><span>Status</span>
  </div>
  <?php foreach ($rows as $t): ?>
    <div class="trow" style="<?= $cols ?>;padding-block:11px">
      <span class="strong"><?= e($t['waktu']) ?></span>
      <span class="mono"><?= e($t['kode']) ?></span>
      <span><?= e($t['kasir']) ?></span>
      <span class="muted"><?= e($t['shift']) ?></span>
      <span class="strong"><?= e(rp($t['total'])) ?></span>
      <span>
        <?php if ($t['status'] === 'lunas'): ?><span class="badge badge-ok">Lunas</span>
        <?php else: ?><span class="badge badge-bad">Dibatalkan</span><?php endif; ?>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><div class="empty">Tidak ada transaksi pada filter ini.</div><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
