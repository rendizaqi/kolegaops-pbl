<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/shift.php';

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$shiftId = (int) ($_GET['id'] ?? 0);

// Detail satu shift (?id=...) atau daftar shift pada satu tanggal.
$detail = false;
if ($shiftId > 0) {
    $stmt = $pdo->prepare("SELECT s.*, u.nama AS kasir FROM shift s JOIN users u ON u.id = s.kasir_id WHERE s.id = :id");
    $stmt->execute(['id' => $shiftId]);
    $detail = $stmt->fetch();
}

if ($detail) {
    $page_title = "Laporan Shift " . $detail['nama'];
    $statistik = statistik_shift($pdo, (int) $detail['id']);
    $kas = kas_diharapkan($detail, $statistik);
    $uangFisik = $detail['uang_fisik'] !== null ? (int) $detail['uang_fisik'] : null;

    $stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE shift_id = :id ORDER BY id");
    $stmt->execute(['id' => $detail['id']]);
    $pengeluaran = $stmt->fetchAll();

    $stmt = $pdo->prepare("SELECT * FROM transaksi WHERE shift_id = :id AND status <> 'open' ORDER BY id");
    $stmt->execute(['id' => $detail['id']]);
    $daftarTransaksi = $stmt->fetchAll();
} else {
    $page_title = "Laporan Per Shift";
    $stmt = $pdo->prepare("SELECT s.*, u.nama AS kasir FROM shift s JOIN users u ON u.id = s.kasir_id WHERE s.tanggal = :tanggal ORDER BY s.id");
    $stmt->execute(['tanggal' => $tanggal]);
    $daftarShift = $stmt->fetchAll();
}

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title"><?php echo $detail ? 'Laporan Shift ' . e($detail['nama']) : 'Laporan Per Shift'; ?></h1>
                <span class="meta">
                    <?php if ($detail): ?>
                        <?php echo e(tanggal_id($detail['tanggal'])); ?> &middot; Kasir <?php echo e($detail['kasir']); ?> &middot;
                        <?php echo jam($detail['buka_at']); ?> &ndash; <?php echo $detail['tutup_at'] ? jam($detail['tutup_at']) : 'berjalan'; ?>
                    <?php else: ?>
                        <?php echo e(tanggal_id($tanggal)); ?>
                    <?php endif; ?>
                </span>
            </div>
            <?php if ($detail): ?>
            <a class="btn btn-outline" href="shift.php?tanggal=<?php echo e($detail['tanggal']); ?>">&larr; Semua shift</a>
            <?php endif; ?>
        </div>

        <?php if (!$detail): ?>
        <form class="filters" method="get" action="shift.php">
            <div class="field">
                <span class="label">Jenis laporan</span>
                <span class="seg">
                    <a class="seg-opt is-active" href="shift.php?tanggal=<?php echo e($tanggal); ?>">Per shift</a>
                    <?php if ($pemilik): ?><a class="seg-opt" href="harian.php?tanggal=<?php echo e($tanggal); ?>">Harian</a><?php endif; ?>
                </span>
            </div>
            <p class="field">
                <label class="label" for="tanggal">Tanggal</label>
                <input class="input" type="date" id="tanggal" name="tanggal" value="<?php echo e($tanggal); ?>">
            </p>
            <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
        </form>
        <?php endif; ?>

        <div class="content">
        <?php if ($detail): ?>
            <div class="cards4">
                <div class="stat stat-primary">
                    <span class="stat-label">Total penjualan</span>
                    <span class="stat-value"><?php echo e(rp($statistik['total'])); ?></span>
                    <span class="stat-note"><?php echo $statistik['jumlah']; ?> transaksi lunas<?php echo $statistik['batal'] ? ' &middot; ' . $statistik['batal'] . ' batal' : ''; ?></span>
                </div>
                <div class="stat"><span class="stat-label">Total tunai</span><span class="stat-value"><?php echo e(rp($statistik['tunai'])); ?></span></div>
                <div class="stat"><span class="stat-label">Total QRIS</span><span class="stat-value"><?php echo e(rp($statistik['qris'])); ?></span><span class="stat-note">Cocokkan dengan laporan DANA</span></div>
                <div class="stat"><span class="stat-label">Pengeluaran</span><span class="stat-value"><?php echo e(rp($statistik['pengeluaran'])); ?></span></div>
            </div>

            <div class="two-col">
                <section class="panel box">
                    <h2 class="box-title">Hitungan uang laci</h2>
                    <table class="tabel tabel-kecil">
                        <tbody>
                            <tr><td>Modal awal shift</td><td class="strong kanan"><?php echo e(rp($detail['modal_awal'])); ?></td></tr>
                            <tr><td>+ Penjualan tunai</td><td class="strong kanan"><?php echo e(rp($statistik['tunai'])); ?></td></tr>
                            <tr><td>&minus; Pengeluaran</td><td class="strong kanan"><?php echo e(rp($statistik['pengeluaran'])); ?></td></tr>
                            <tr class="total"><td>Uang seharusnya</td><td class="strong kanan"><?php echo e(rp($kas)); ?></td></tr>
                            <tr><td>Uang fisik di laci (akhir shift)</td><td class="strong kanan"><?php echo $uangFisik === null ? '&ndash;' : e(rp($uangFisik)); ?></td></tr>
                            <tr class="total"><td>Selisih</td><td class="kanan"><?php echo $uangFisik === null ? '<span class="muted">Shift belum ditutup</span>' : label_selisih($uangFisik - $kas); ?></td></tr>
                        </tbody>
                    </table>
                    <?php if ($detail['catatan']): ?>
                    <p class="alert alert-warn">Catatan: <?php echo e($detail['catatan']); ?></p>
                    <?php endif; ?>
                </section>

                <section class="panel box">
                    <h2 class="box-title"><?php echo $pemilik ? 'Untung rugi shift' : 'Pengeluaran shift'; ?></h2>
                    <?php if ($pemilik): ?>
                    <table class="tabel tabel-kecil">
                        <tbody>
                            <tr><td>Penjualan</td><td class="strong kanan"><?php echo e(rp($statistik['total'])); ?></td></tr>
                            <tr><td>&minus; HPP</td><td class="strong kanan"><?php echo e(rp($statistik['hpp'])); ?></td></tr>
                            <tr class="total"><td>Laba kotor</td><td class="strong kanan"><?php echo e(rp($statistik['laba'])); ?></td></tr>
                            <tr><td>&minus; Pengeluaran</td><td class="strong kanan"><?php echo e(rp($statistik['pengeluaran'])); ?></td></tr>
                            <tr class="total"><td>Laba setelah pengeluaran</td><td class="strong kanan"><?php echo e(rp($statistik['laba'] - $statistik['pengeluaran'])); ?></td></tr>
                        </tbody>
                    </table>
                    <?php endif; ?>
                    <table class="tabel tabel-kecil">
                        <tbody>
                            <?php foreach ($pengeluaran as $k): ?>
                            <tr><td class="muted"><?php echo jam($k['dibuat']); ?></td><td class="name"><?php echo e($k['keterangan']); ?></td><td class="strong kanan"><?php echo e(rp($k['nominal'])); ?></td></tr>
                            <?php endforeach; ?>
                            <?php if (!$pengeluaran): ?><tr><td colspan="3" class="empty">Tidak ada pengeluaran.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </section>
            </div>

            <section class="panel">
                <div class="dash-card-head"><h2 class="dash-card-title">Transaksi shift ini</h2></div>
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Waktu</th><th>No. transaksi</th><th>Metode</th><th>Total</th><th>Status</th><th>Struk</th></tr></thead>
                    <tbody>
                        <?php foreach ($daftarTransaksi as $t): ?>
                        <tr>
                            <td class="strong"><?php echo jam($t['dibuat']); ?></td>
                            <td class="mono"><?php echo e($t['kode']); ?></td>
                            <td><?php echo $t['metode'] === 'qris' ? '<span class="badge badge-qris">QRIS</span>' : 'Tunai'; ?></td>
                            <td class="strong"><?php echo e(rp($t['total'])); ?></td>
                            <td><?php echo $t['status'] === 'lunas' ? '<span class="badge badge-ok">Lunas</span>' : '<span class="badge badge-bad">Batal</span>'; ?></td>
                            <td><a class="strong" href="../transaksi/struk.php?id=<?php echo (int) $t['id']; ?>" target="_blank">Struk</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$daftarTransaksi): ?><tr><td colspan="6" class="empty">Belum ada transaksi.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>

        <?php else: ?>
            <section class="panel">
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Shift</th><th>Kasir</th><th>Tunai</th><th>QRIS</th><th>Total</th><th>Selisih laci</th><th>Laporan</th></tr></thead>
                    <tbody>
                        <?php foreach ($daftarShift as $s):
                            $st = statistik_shift($pdo, (int) $s['id']);
                            $selisih = $s['uang_fisik'] !== null ? (int) $s['uang_fisik'] - kas_diharapkan($s, $st) : null;
                        ?>
                        <tr>
                            <td class="strong"><?php echo e($s['nama']); ?></td>
                            <td><?php echo e($s['kasir']); ?></td>
                            <td><?php echo e(rp($st['tunai'])); ?></td>
                            <td><?php echo e(rp($st['qris'])); ?></td>
                            <td class="strong"><?php echo e(rp($st['total'])); ?></td>
                            <td><?php echo $selisih === null ? '<span class="badge badge-status badge-menipis">&#9679; Berjalan</span>' : label_selisih($selisih); ?></td>
                            <td><a class="strong" href="shift.php?id=<?php echo (int) $s['id']; ?>">Buka &rarr;</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$daftarShift): ?><tr><td colspan="7" class="empty">Tidak ada shift pada tanggal ini.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>
        <?php endif; ?>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
