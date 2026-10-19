<?php
require __DIR__ . '/includes/functions.php';
$u = require_login();
$db = pdo();
$isOwner = $u['role'] === 'owner';
$shift = current_shift();
$milikku = $shift && ((int) $shift['kasir_id'] === (int) $u['id'] || $isOwner);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';

    if ($act === 'buka') {
        $nama = $_POST['nama'] ?? '';
        $modal = (int) preg_replace('/\D/', '', (string) ($_POST['modal'] ?? '0'));
        if ($shift) {
            flash_set('err', 'Masih ada shift ' . $shift['nama'] . ' yang berjalan. Tutup dulu sebelum membuka shift baru.');
        } elseif (!in_array($nama, shift_names(), true)) {
            flash_set('err', 'Pilih shift Pagi, Siang, atau Malam.');
        } else {
            $db->prepare("INSERT INTO shift (nama, kasir_id, kasir, tanggal, buka_at, modal_awal, status) VALUES (?, ?, ?, ?, ?, ?, 'buka')")
               ->execute([$nama, $u['id'], $u['nama'], date('Y-m-d'), date('Y-m-d H:i:s'), $modal]);
            flash_set('ok', 'Shift ' . $nama . ' dibuka dengan kasir ' . $u['nama'] . '. Modal awal ' . rp($modal) . '.');
            redirect('kasir.php');
        }
        redirect('shift.php');
    }

    if (!$shift || !$milikku) {
        flash_set('err', 'Hanya kasir shift ini atau pemilik yang bisa mengubah shift.');
        redirect('shift.php');
    }

    if ($act === 'pengeluaran') {
        $ket = trim((string) ($_POST['keterangan'] ?? ''));
        $nom = (int) preg_replace('/\D/', '', (string) ($_POST['nominal'] ?? '0'));
        if ($ket === '' || $nom <= 0) {
            flash_set('err', 'Isi keterangan dan nominal pengeluaran.');
        } else {
            $db->prepare('INSERT INTO pengeluaran (shift_id, keterangan, nominal, waktu) VALUES (?, ?, ?, ?)')
               ->execute([$shift['id'], mb_substr($ket, 0, 100), $nom, date('H:i')]);
            flash_set('ok', 'Pengeluaran "' . $ket . '" ' . rp($nom) . ' dicatat.');
        }
        redirect('shift.php');
    }

    if ($act === 'hapus_pengeluaran') {
        $db->prepare('DELETE FROM pengeluaran WHERE id = ? AND shift_id = ?')->execute([(int) ($_POST['id'] ?? 0), $shift['id']]);
        redirect('shift.php');
    }

    if ($act === 'tutup') {
        $open = $db->prepare("SELECT COUNT(*) FROM transaksi WHERE shift_id = ? AND status = 'open'");
        $open->execute([$shift['id']]);
        $fisikRaw = preg_replace('/\D/', '', (string) ($_POST['uang_fisik'] ?? ''));
        if ((int) $open->fetchColumn() > 0) {
            flash_set('err', 'Masih ada open bill. Selesaikan atau hapus dulu sebelum menutup shift.');
        } elseif ($fisikRaw === '') {
            flash_set('err', 'Isi jumlah uang tunai di laci saat ini.');
        } else {
            $db->prepare("UPDATE shift SET status = 'tutup', tutup_at = ?, uang_fisik = ?, catatan = ? WHERE id = ?")
               ->execute([date('Y-m-d H:i:s'), (int) $fisikRaw, mb_substr(trim((string) ($_POST['catatan'] ?? '')), 0, 255) ?: null, $shift['id']]);
            flash_set('ok', 'Shift ' . $shift['nama'] . ' ditutup. Ini laporan shift-nya.');
            redirect('laporan.php?shift=' . (int) $shift['id']);
        }
        redirect('shift.php');
    }
    redirect('shift.php');
}

$stats = $shift ? shift_stats($db, (int) $shift['id']) : null;
$kas = $shift ? kas_diharapkan($shift, $stats) : 0;
$keluar = [];
if ($shift) {
    $st = $db->prepare('SELECT * FROM pengeluaran WHERE shift_id = ? ORDER BY id');
    $st->execute([$shift['id']]);
    $keluar = $st->fetchAll();
}
$modalSaran = (int) $db->query("SELECT COALESCE(uang_fisik,0) FROM shift WHERE status = 'tutup' ORDER BY id DESC LIMIT 1")->fetchColumn();
$riwayat = $db->query('SELECT * FROM shift ORDER BY id DESC LIMIT 10')->fetchAll();

$page = 'shift';
$title = 'Shift';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Shift</h1>
    <span class="meta">1 shift = 8 jam, satu kasir. Tutup shift paling lambat pukul 00:00.</span>
  </div>
</div>

<div class="content">
<?php if (!$shift): ?>
  <section class="panel box">
    <h2 class="box-title">Buka shift</h2>
    <form method="post" class="form-grid"><?= csrf_field() ?>
      <input type="hidden" name="act" value="buka">
      <div class="field">
        <span class="label">Shift</span>
        <div class="seg">
          <?php foreach (shift_names() as $i => $n): ?>
            <label class="seg-opt radio-seg"><input type="radio" name="nama" value="<?= $n ?>" <?= $i === 0 ? 'checked' : '' ?>><span><?= $n ?></span></label>
          <?php endforeach; ?>
        </div>
      </div>
      <label class="field">
        <span class="label">Modal awal di laci (Rp)</span>
        <input class="input input-strong" name="modal" inputmode="numeric" value="<?= $modalSaran ? number_format($modalSaran, 0, ',', '.') : '' ?>" placeholder="0">
        <span class="meta"><?= $modalSaran ? 'Terisi dari uang akhir shift sebelumnya. Ubah bila berbeda.' : 'Hitung uang tunai yang ada di laci sebelum mulai.' ?></span>
      </label>
      <div class="field">
        <span class="label">Kasir shift</span>
        <div class="static-box"><?= e($u['nama']) ?> (akun yang sedang login)</div>
        <span class="meta">Untuk memakai akun lain, keluar lalu masuk dengan akun tersebut.</span>
      </div>
      <button class="btn btn-primary" type="submit" style="justify-content:space-between"><span>Buka shift &amp; mulai kasir</span><span>→</span></button>
    </form>
  </section>
<?php else: ?>
  <div class="cards4">
    <div class="stat stat-primary"><span class="stat-label">Shift <?= e($shift['nama']) ?></span>
      <span class="stat-value" style="font-size:22px"><?= e($shift['kasir']) ?></span>
      <span class="stat-note">Dibuka <?= e(substr($shift['buka_at'], 11, 5)) ?> · modal <?= e(rp($shift['modal_awal'])) ?></span></div>
    <div class="stat"><span class="stat-label">Penjualan tunai</span><span class="stat-value"><?= e(rp($stats['tunai'])) ?></span></div>
    <div class="stat"><span class="stat-label">Penjualan QRIS</span><span class="stat-value"><?= e(rp($stats['qris'])) ?></span><span class="stat-note">Masuk ke HP pemilik</span></div>
    <div class="stat"><span class="stat-label">Pengeluaran</span><span class="stat-value"><?= e(rp($stats['pengeluaran'])) ?></span></div>
  </div>

  <div class="two-col">
    <section class="panel box">
      <h2 class="box-title">Pengeluaran shift ini</h2>
      <p class="meta" style="margin:0">Catat belanja yang memakai uang laci, mis. es batu atau bahan habis.</p>
      <?php if ($milikku): ?>
      <form method="post" class="row-gap wrap"><?= csrf_field() ?>
        <input type="hidden" name="act" value="pengeluaran">
        <input class="input" name="keterangan" placeholder="Keterangan, mis. Es batu" style="flex:2;min-width:160px" required>
        <input class="input" name="nominal" placeholder="Nominal" inputmode="numeric" style="flex:1;min-width:110px" required>
        <button class="btn btn-outline btn-md" type="submit">Catat</button>
      </form>
      <?php endif; ?>
      <div class="mini-table">
        <?php foreach ($keluar as $k): ?>
          <div class="mini-row">
            <span class="muted"><?= e($k['waktu']) ?></span><span class="name"><?= e($k['keterangan']) ?></span>
            <span class="strong"><?= e(rp($k['nominal'])) ?></span>
            <?php if ($milikku): ?>
              <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="hapus_pengeluaran"><input type="hidden" name="id" value="<?= (int) $k['id'] ?>">
                <button class="order-line-remove" title="Hapus">×</button></form>
            <?php endif; ?>
          </div>
        <?php endforeach; ?>
        <?php if (!$keluar): ?><div class="empty" style="padding:12px 0">Belum ada pengeluaran.</div><?php endif; ?>
      </div>
    </section>

    <section class="panel box">
      <h2 class="box-title">Tutup shift</h2>
      <div class="mini-table">
        <div class="mini-row"><span>Modal awal</span><span></span><span class="strong"><?= e(rp($shift['modal_awal'])) ?></span></div>
        <div class="mini-row"><span>+ Penjualan tunai</span><span></span><span class="strong"><?= e(rp($stats['tunai'])) ?></span></div>
        <div class="mini-row"><span>− Pengeluaran</span><span></span><span class="strong"><?= e(rp($stats['pengeluaran'])) ?></span></div>
        <div class="mini-row total"><span>Uang tunai seharusnya di laci</span><span></span><span class="strong"><?= e(rp($kas)) ?></span></div>
      </div>
      <?php if ($milikku): ?>
      <form method="post" class="form-grid"><?= csrf_field() ?>
        <input type="hidden" name="act" value="tutup">
        <label class="field"><span class="label">Uang tunai di laci sekarang (Rp)</span>
          <input class="input input-strong" name="uang_fisik" inputmode="numeric" placeholder="Hitung uang fisik" required></label>
        <label class="field"><span class="label">Catatan (opsional)</span>
          <input class="input" name="catatan" maxlength="255" placeholder="Mis. kurang Rp 2.000, kembalian pelanggan"></label>
        <button class="btn btn-primary" type="submit" style="justify-content:space-between"><span>Tutup shift &amp; lihat laporan</span><span>→</span></button>
      </form>
      <?php else: ?>
        <div class="alert alert-warn" style="margin:0">Hanya <?= e($shift['kasir']) ?> atau pemilik yang bisa menutup shift ini.</div>
      <?php endif; ?>
    </section>
  </div>
<?php endif; ?>

  <section class="panel table-block">
    <div class="dash-card-head"><h2 class="dash-card-title">Shift terakhir</h2></div>
    <?php $cols = 'grid-template-columns:1.1fr .8fr 1fr 1.2fr .9fr .9fr'; ?>
    <div class="trow thead" style="<?= $cols ?>"><span>Tanggal</span><span>Shift</span><span>Kasir</span><span>Waktu</span><span>Status</span><span>Laporan</span></div>
    <?php foreach ($riwayat as $r): ?>
      <div class="trow" style="<?= $cols ?>">
        <span class="strong"><?= e(tgl_id($r['tanggal'])) ?></span><span><?= e($r['nama']) ?></span><span><?= e($r['kasir']) ?></span>
        <span class="muted"><?= e(substr($r['buka_at'], 11, 5)) ?> – <?= $r['tutup_at'] ? e(substr($r['tutup_at'], 11, 5)) : '…' ?></span>
        <span><?= $r['status'] === 'buka' ? '<span class="badge badge-menipis badge-status">● Berjalan</span>' : '<span class="badge badge-ok">Ditutup</span>' ?></span>
        <span><a href="laporan.php?shift=<?= (int) $r['id'] ?>" class="strong">Lihat →</a></span>
      </div>
    <?php endforeach; ?>
    <?php if (!$riwayat): ?><div class="empty">Belum ada shift.</div><?php endif; ?>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
