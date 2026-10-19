<?php
require __DIR__ . '/includes/functions.php';
$u = require_login();
$db = pdo();
$isOwner = $u['role'] === 'owner';

$tipe = ($_GET['tipe'] ?? 'shift') === 'harian' && $isOwner ? 'harian' : 'shift';
$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$shiftId = (int) ($_GET['shift'] ?? 0);

$detail = null;
if ($shiftId) {
    $st = $db->prepare('SELECT * FROM shift WHERE id = ?');
    $st->execute([$shiftId]);
    $detail = $st->fetch() ?: null;
}

$page = 'laporan';
$title = 'Laporan';
$selisihLabel = function (int $s): string {
    if ($s === 0) return '<span class="selisih selisih-ok">Pas</span>';
    return $s > 0
        ? '<span class="selisih selisih-lebih">Lebih ' . e(rp($s)) . '</span>'
        : '<span class="selisih selisih-kurang">Kurang ' . e(rp(-$s)) . '</span>';
};

// ── Data ──
if ($detail) {
    $stats = shift_stats($db, (int) $detail['id']);
    $kas = kas_diharapkan($detail, $stats);
    $fisik = $detail['uang_fisik'] !== null ? (int) $detail['uang_fisik'] : null;
    $st = $db->prepare('SELECT * FROM pengeluaran WHERE shift_id = ? ORDER BY id');
    $st->execute([$detail['id']]);
    $keluar = $st->fetchAll();
    $st = $db->prepare("SELECT * FROM transaksi WHERE shift_id = ? AND status <> 'open' ORDER BY id");
    $st->execute([$detail['id']]);
    $trx = $st->fetchAll();
} elseif ($tipe === 'shift') {
    $st = $db->prepare('SELECT * FROM shift WHERE tanggal = ? ORDER BY id');
    $st->execute([$tanggal]);
    $shifts = $st->fetchAll();
} else {
    $st = $db->prepare('SELECT * FROM shift WHERE tanggal = ? ORDER BY id');
    $st->execute([$tanggal]);
    $shifts = $st->fetchAll();
    $sum = ['tunai' => 0, 'qris' => 0, 'total' => 0, 'n' => 0, 'hpp' => 0, 'pengeluaran' => 0, 'selisih' => 0];
    $per = [];
    foreach ($shifts as $s) {
        $ss = shift_stats($db, (int) $s['id']);
        $selisih = $s['uang_fisik'] !== null ? (int) $s['uang_fisik'] - kas_diharapkan($s, $ss) : null;
        $per[] = [$s, $ss, $selisih];
        foreach (['tunai', 'qris', 'total', 'n', 'hpp', 'pengeluaran'] as $k) {
            $sum[$k] += $ss[$k];
        }
        $sum['selisih'] += $selisih ?? 0;
    }
    $laba = $sum['total'] - $sum['hpp'];
    $st = $db->prepare("SELECT i.nama, SUM(i.qty) AS qty, SUM(i.qty * i.harga) AS omzet, SUM(i.qty * (i.harga - i.hpp)) AS profit
                        FROM transaksi_item i JOIN transaksi t ON t.id = i.transaksi_id
                        WHERE t.tanggal = ? AND t.status = 'lunas' GROUP BY i.nama ORDER BY qty DESC, omzet DESC LIMIT 5");
    $st->execute([$tanggal]);
    $terlaris = $st->fetchAll();
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title"><?= $detail ? 'Laporan Shift ' . e($detail['nama']) : ($tipe === 'harian' ? 'Laporan Harian' : 'Laporan Per Shift') ?></h1>
    <span class="meta">
      <?php if ($detail): ?><?= e(tgl_id($detail['tanggal'])) ?> · Kasir <?= e($detail['kasir']) ?> ·
        <?= e(substr($detail['buka_at'], 11, 5)) ?> – <?= $detail['tutup_at'] ? e(substr($detail['tutup_at'], 11, 5)) : 'berjalan' ?>
      <?php else: ?><?= e(tgl_id($tanggal)) ?><?php endif; ?>
    </span>
  </div>
  <?php if ($detail): ?><a class="btn btn-outline" href="laporan.php?tanggal=<?= e($detail['tanggal']) ?>">← Semua shift</a><?php endif; ?>
</div>

<?php if (!$detail): ?>
<form class="filters" method="get" action="laporan.php">
  <div class="field"><span class="label">Jenis laporan</span>
    <div class="seg">
      <a class="seg-opt<?= $tipe === 'shift' ? ' is-active' : '' ?>" href="laporan.php?tipe=shift&amp;tanggal=<?= e($tanggal) ?>">Per shift</a>
      <?php if ($isOwner): ?><a class="seg-opt<?= $tipe === 'harian' ? ' is-active' : '' ?>" href="laporan.php?tipe=harian&amp;tanggal=<?= e($tanggal) ?>">Harian</a><?php endif; ?>
    </div></div>
  <label class="field"><span class="label">Tanggal</span>
    <input class="input" type="date" name="tanggal" value="<?= e($tanggal) ?>" style="font-weight:600;padding-inline:10px"></label>
  <input type="hidden" name="tipe" value="<?= $tipe ?>">
  <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
</form>
<?php endif; ?>

<div class="content">
<?php if ($detail): // ── Detail satu shift ── ?>
  <div class="cards4">
    <div class="stat stat-primary"><span class="stat-label">Total penjualan</span><span class="stat-value"><?= e(rp($stats['total'])) ?></span><span class="stat-note"><?= $stats['n'] ?> transaksi lunas<?= $stats['batal'] ? ' · ' . $stats['batal'] . ' batal' : '' ?></span></div>
    <div class="stat"><span class="stat-label">Total tunai</span><span class="stat-value"><?= e(rp($stats['tunai'])) ?></span></div>
    <div class="stat"><span class="stat-label">Total QRIS</span><span class="stat-value"><?= e(rp($stats['qris'])) ?></span><span class="stat-note">Cocokkan dengan laporan DANA</span></div>
    <div class="stat"><span class="stat-label">Pengeluaran</span><span class="stat-value"><?= e(rp($stats['pengeluaran'])) ?></span></div>
  </div>

  <div class="two-col">
    <section class="panel box">
      <h2 class="box-title">Hitungan uang laci</h2>
      <div class="mini-table">
        <div class="mini-row"><span>Modal awal shift</span><span></span><span class="strong"><?= e(rp($detail['modal_awal'])) ?></span></div>
        <div class="mini-row"><span>+ Penjualan tunai</span><span></span><span class="strong"><?= e(rp($stats['tunai'])) ?></span></div>
        <div class="mini-row"><span>− Pengeluaran</span><span></span><span class="strong"><?= e(rp($stats['pengeluaran'])) ?></span></div>
        <div class="mini-row total"><span>Uang seharusnya</span><span></span><span class="strong"><?= e(rp($kas)) ?></span></div>
        <div class="mini-row"><span>Uang fisik di laci (akhir shift)</span><span></span><span class="strong"><?= $fisik === null ? '–' : e(rp($fisik)) ?></span></div>
        <div class="mini-row total"><span>Selisih</span><span></span><span><?= $fisik === null ? '<span class="muted">Shift belum ditutup</span>' : $selisihLabel($fisik - $kas) ?></span></div>
      </div>
      <?php if ($detail['catatan']): ?><div class="alert alert-warn" style="margin:0">Catatan: <?= e($detail['catatan']) ?></div><?php endif; ?>
    </section>

    <section class="panel box">
      <h2 class="box-title"><?= $isOwner ? 'Untung rugi shift' : 'Pengeluaran shift' ?></h2>
      <?php if ($isOwner): ?>
        <div class="mini-table">
          <div class="mini-row"><span>Penjualan</span><span></span><span class="strong"><?= e(rp($stats['total'])) ?></span></div>
          <div class="mini-row"><span>− HPP</span><span></span><span class="strong"><?= e(rp($stats['hpp'])) ?></span></div>
          <div class="mini-row total"><span>Laba kotor</span><span></span><span class="strong"><?= e(rp($stats['laba'])) ?></span></div>
          <div class="mini-row"><span>− Pengeluaran</span><span></span><span class="strong"><?= e(rp($stats['pengeluaran'])) ?></span></div>
          <div class="mini-row total"><span>Laba setelah pengeluaran</span><span></span><span class="strong"><?= e(rp($stats['laba'] - $stats['pengeluaran'])) ?></span></div>
        </div>
      <?php endif; ?>
      <div class="mini-table">
        <?php foreach ($keluar as $k): ?>
          <div class="mini-row"><span class="muted"><?= e($k['waktu']) ?></span><span class="name"><?= e($k['keterangan']) ?></span><span class="strong"><?= e(rp($k['nominal'])) ?></span></div>
        <?php endforeach; ?>
        <?php if (!$keluar): ?><div class="empty" style="padding:8px 0">Tidak ada pengeluaran.</div><?php endif; ?>
      </div>
    </section>
  </div>

  <section class="panel table-block">
    <div class="dash-card-head"><h2 class="dash-card-title">Transaksi shift ini</h2></div>
    <?php $cols = 'grid-template-columns:.7fr 1.2fr .9fr 1fr .9fr .7fr'; ?>
    <div class="trow thead" style="<?= $cols ?>"><span>Waktu</span><span>No. transaksi</span><span>Metode</span><span>Total</span><span>Status</span><span>Struk</span></div>
    <?php foreach ($trx as $t): ?>
      <div class="trow" style="<?= $cols ?>">
        <span class="strong"><?= e($t['waktu']) ?></span><span class="mono"><?= e($t['kode']) ?></span>
        <span><?= $t['metode'] === 'qris' ? '<span class="badge badge-qris">QRIS</span>' : 'Tunai' ?></span>
        <span class="strong"><?= e(rp($t['total'])) ?></span>
        <span><?= $t['status'] === 'lunas' ? '<span class="badge badge-ok">Lunas</span>' : '<span class="badge badge-bad">Batal</span>' ?></span>
        <a class="strong" href="struk.php?id=<?= (int) $t['id'] ?>" target="_blank">Struk</a>
      </div>
    <?php endforeach; ?>
    <?php if (!$trx): ?><div class="empty">Belum ada transaksi.</div><?php endif; ?>
  </section>

<?php elseif ($tipe === 'shift'): // ── Daftar shift pada tanggal ── ?>
  <section class="panel table-block">
    <?php $cols = 'grid-template-columns:.8fr 1fr 1fr 1fr 1fr 1fr .8fr'; ?>
    <div class="trow thead" style="<?= $cols ?>"><span>Shift</span><span>Kasir</span><span>Tunai</span><span>QRIS</span><span>Total</span><span>Selisih laci</span><span>Laporan</span></div>
    <?php foreach ($shifts as $s):
      $ss = shift_stats($db, (int) $s['id']);
      $sel = $s['uang_fisik'] !== null ? (int) $s['uang_fisik'] - kas_diharapkan($s, $ss) : null;
    ?>
      <div class="trow" style="<?= $cols ?>">
        <span class="strong"><?= e($s['nama']) ?></span><span><?= e($s['kasir']) ?></span>
        <span><?= e(rp($ss['tunai'])) ?></span><span><?= e(rp($ss['qris'])) ?></span><span class="strong"><?= e(rp($ss['total'])) ?></span>
        <span><?= $sel === null ? '<span class="badge badge-menipis badge-status">● Berjalan</span>' : $selisihLabel($sel) ?></span>
        <a class="strong" href="laporan.php?shift=<?= (int) $s['id'] ?>">Buka →</a>
      </div>
    <?php endforeach; ?>
    <?php if (!$shifts): ?><div class="empty">Tidak ada shift pada tanggal ini.</div><?php endif; ?>
  </section>

<?php else: // ── Harian (pemilik) ── ?>
  <div class="cards4">
    <div class="stat stat-primary"><span class="stat-label">Total penjualan</span><span class="stat-value"><?= e(rp($sum['total'])) ?></span><span class="stat-note"><?= $sum['n'] ?> transaksi lunas</span></div>
    <div class="stat"><span class="stat-label">Total HPP</span><span class="stat-value"><?= e(rp($sum['hpp'])) ?></span></div>
    <div class="stat"><span class="stat-label">Laba kotor</span><span class="stat-value"><?= e(rp($laba)) ?></span><span class="stat-note">Penjualan − HPP</span></div>
    <div class="stat"><span class="stat-label">Laba setelah pengeluaran</span><span class="stat-value"><?= e(rp($laba - $sum['pengeluaran'])) ?></span><span class="stat-note">Pengeluaran <?= e(rp($sum['pengeluaran'])) ?></span></div>
  </div>
  <div class="cards4">
    <div class="stat"><span class="stat-label">Total tunai</span><span class="stat-value"><?= e(rp($sum['tunai'])) ?></span></div>
    <div class="stat"><span class="stat-label">Total QRIS</span><span class="stat-value"><?= e(rp($sum['qris'])) ?></span><span class="stat-note">Cocokkan dengan laporan DANA</span></div>
    <div class="stat"><span class="stat-label">Selisih uang laci (semua shift)</span><span class="stat-value" style="font-size:22px"><?= $selisihLabel($sum['selisih']) ?></span></div>
    <div class="stat"><span class="stat-label">Jumlah shift</span><span class="stat-value"><?= count($per) ?></span></div>
  </div>

  <section class="panel table-block">
    <div class="dash-card-head"><h2 class="dash-card-title">Rincian per shift</h2></div>
    <?php $cols = 'grid-template-columns:.7fr .9fr .9fr .9fr .9fr .9fr .9fr .9fr'; ?>
    <div class="trow thead" style="<?= $cols ?>"><span>Shift</span><span>Kasir</span><span>Tunai</span><span>QRIS</span><span>Total</span><span>HPP</span><span>Pengeluaran</span><span>Selisih</span></div>
    <?php foreach ($per as [$s, $ss, $sel]): ?>
      <div class="trow" style="<?= $cols ?>">
        <a class="strong" href="laporan.php?shift=<?= (int) $s['id'] ?>"><?= e($s['nama']) ?></a><span><?= e($s['kasir']) ?></span>
        <span><?= e(rp($ss['tunai'])) ?></span><span><?= e(rp($ss['qris'])) ?></span><span class="strong"><?= e(rp($ss['total'])) ?></span>
        <span class="muted"><?= e(rp($ss['hpp'])) ?></span><span><?= e(rp($ss['pengeluaran'])) ?></span>
        <span><?= $sel === null ? '–' : $selisihLabel($sel) ?></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$per): ?><div class="empty">Tidak ada shift pada tanggal ini.</div><?php endif; ?>
  </section>

  <section class="panel table-block">
    <div class="dash-card-head"><h2 class="dash-card-title">Menu terlaris</h2></div>
    <?php $cols = 'grid-template-columns:2fr 1fr 1fr 1fr'; ?>
    <div class="trow thead" style="<?= $cols ?>"><span>Menu</span><span>Terjual</span><span>Omzet</span><span>Profit</span></div>
    <?php foreach ($terlaris as $t): ?>
      <div class="trow" style="<?= $cols ?>"><span class="name"><?= e($t['nama']) ?></span><span><?= (int) $t['qty'] ?></span>
        <span><?= e(rp($t['omzet'])) ?></span><span class="profit"><?= e(rp($t['profit'])) ?></span></div>
    <?php endforeach; ?>
    <?php if (!$terlaris): ?><div class="empty">Belum ada penjualan.</div><?php endif; ?>
  </section>
<?php endif; ?>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
