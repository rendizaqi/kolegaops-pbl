<?php
require __DIR__ . '/includes/functions.php';
$u = require_login();
$db = pdo();

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$shiftF = $_GET['shift'] ?? '';
$metodeF = $_GET['metode'] ?? '';
$statusF = $_GET['status'] ?? '';

$sql = 'SELECT * FROM transaksi WHERE tanggal = ?';
$args = [$tanggal];
if (in_array($shiftF, shift_names(), true)) { $sql .= ' AND shift = ?'; $args[] = $shiftF; }
if (in_array($metodeF, ['tunai', 'qris'], true)) { $sql .= ' AND metode = ?'; $args[] = $metodeF; }
if (in_array($statusF, ['lunas', 'open', 'batal'], true)) { $sql .= ' AND status = ?'; $args[] = $statusF; }
$sql .= ' ORDER BY id DESC';
$st = $db->prepare($sql);
$st->execute($args);
$rows = $st->fetchAll();

$lunas = array_filter($rows, fn($t) => $t['status'] === 'lunas');
$sum = array_sum(array_map(fn($t) => (int) $t['total'], $lunas));

if (($_GET['export'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="riwayat-' . $tanggal . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Waktu', 'No. transaksi', 'Kasir', 'Shift', 'Metode', 'Total', 'Status']);
    foreach ($rows as $t) {
        fputcsv($out, [$t['waktu'], $t['kode'], $t['kasir'], $t['shift'], $t['metode'], $t['total'], $t['status']]);
    }
    fclose($out);
    exit;
}

$exportUrl = 'riwayat.php?' . http_build_query(['tanggal' => $tanggal, 'shift' => $shiftF, 'metode' => $metodeF, 'status' => $statusF, 'export' => 'csv']);
$cols = 'grid-template-columns:.7fr 1.1fr 1fr .8fr .8fr 1fr .9fr .7fr';
$page = 'riwayat';
$title = 'Riwayat Transaksi';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Riwayat Transaksi</h1>
    <span class="meta"><?= e(tgl_id($tanggal)) ?> · <?= count($rows) ?> transaksi · lunas <?= e(rp($sum)) ?></span>
  </div>
  <a class="btn btn-outline" href="<?= e($exportUrl) ?>">Ekspor CSV</a>
</div>

<form class="filters" method="get" action="riwayat.php">
  <label class="field"><span class="label">Tanggal</span>
    <input class="input" type="date" name="tanggal" value="<?= e($tanggal) ?>" style="font-weight:600;padding-inline:10px"></label>
  <label class="field"><span class="label">Shift</span>
    <select class="input" name="shift" style="min-width:120px">
      <option value="">Semua</option>
      <?php foreach (shift_names() as $n): ?><option <?= $shiftF === $n ? 'selected' : '' ?>><?= $n ?></option><?php endforeach; ?>
    </select></label>
  <label class="field"><span class="label">Metode</span>
    <select class="input" name="metode" style="min-width:120px">
      <option value="">Semua</option>
      <option value="tunai" <?= $metodeF === 'tunai' ? 'selected' : '' ?>>Tunai</option>
      <option value="qris" <?= $metodeF === 'qris' ? 'selected' : '' ?>>QRIS</option>
    </select></label>
  <label class="field"><span class="label">Status</span>
    <select class="input" name="status" style="min-width:130px">
      <option value="">Semua</option>
      <option value="lunas" <?= $statusF === 'lunas' ? 'selected' : '' ?>>Lunas</option>
      <option value="open" <?= $statusF === 'open' ? 'selected' : '' ?>>Open bill</option>
      <option value="batal" <?= $statusF === 'batal' ? 'selected' : '' ?>>Dibatalkan</option>
    </select></label>
  <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
</form>

<div class="panel table-wrap" style="margin-top:16px">
  <div class="trow thead" style="<?= $cols ?>">
    <span>Waktu</span><span>No. transaksi</span><span>Kasir</span><span>Shift</span><span>Metode</span><span>Total</span><span>Status</span><span>Struk</span>
  </div>
  <?php foreach ($rows as $t): ?>
    <div class="trow" style="<?= $cols ?>;padding-block:11px">
      <span class="strong"><?= e($t['waktu']) ?></span>
      <span class="mono"><?= e($t['kode']) ?></span>
      <span><?= e($t['kasir']) ?></span>
      <span class="muted"><?= e($t['shift']) ?></span>
      <span><?= $t['status'] === 'open' ? '–' : ($t['metode'] === 'qris' ? '<span class="badge badge-qris">QRIS</span>' : 'Tunai') ?></span>
      <span class="strong"><?= e(rp($t['total'])) ?></span>
      <span>
        <?php if ($t['status'] === 'lunas'): ?><span class="badge badge-ok">Lunas</span>
        <?php elseif ($t['status'] === 'open'): ?><span class="badge badge-warn">Open bill</span>
        <?php else: ?><span class="badge badge-bad">Batal</span><?php endif; ?>
      </span>
      <a class="strong" href="struk.php?id=<?= (int) $t['id'] ?>" target="_blank">Struk</a>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><div class="empty">Tidak ada transaksi pada filter ini.</div><?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
