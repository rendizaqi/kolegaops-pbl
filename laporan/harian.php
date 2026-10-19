<?php
$page_title = "Laporan Transaksi Harian";
$menu_aktif = "laporan";
include __DIR__ . '/../includes/header.php';

$daftarKasir = array_values(array_unique(array_column($transaksi, 'kasir')));

// Hitungan awal (semua shift). JavaScript menghitung ulang saat filter diganti.
$pendapatan = 0;
foreach ($transaksi as $t) {
    if ($t['status'] === 'lunas') {
        $pendapatan += $t['total'];
    }
}
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Laporan Transaksi Harian</h1>
                <span class="meta"><?php echo e(tanggal_id()); ?> &middot; hanya tampilan contoh, tanggal belum menyaring data</span>
            </div>
            <button type="button" class="btn btn-outline" id="btn-ekspor" title="Belum aktif di versi tampilan" disabled>Ekspor CSV</button>
        </div>

        <div class="filters" id="filter-laporan">
            <p class="field">
                <label class="label" for="tanggal">Tanggal</label>
                <input class="input" type="date" id="tanggal" value="<?php echo date('Y-m-d'); ?>">
            </p>
            <div class="field">
                <span class="label">Shift</span>
                <span class="seg" id="seg-shift">
                    <button type="button" class="seg-opt is-active" data-shift="Semua">Semua</button>
                    <button type="button" class="seg-opt" data-shift="Pagi">Pagi</button>
                    <button type="button" class="seg-opt" data-shift="Siang">Siang</button>
                    <button type="button" class="seg-opt" data-shift="Malam">Malam</button>
                </span>
            </div>
            <p class="field">
                <label class="label" for="filter-kasir">Kasir</label>
                <select class="input" id="filter-kasir">
                    <option value="">Semua kasir</option>
                    <?php foreach ($daftarKasir as $k): ?><option><?php echo e($k); ?></option><?php endforeach; ?>
                </select>
            </p>
        </div>

        <div class="content">
            <div class="cards4">
                <div class="stat stat-primary"><span class="stat-label">Total pendapatan</span><span class="stat-value" id="r-pendapatan"><?php echo e(rp($pendapatan)); ?></span><span class="stat-note" id="r-lunas"></span></div>
                <div class="stat"><span class="stat-label">Total transaksi</span><span class="stat-value" id="r-jumlah"><?php echo count($transaksi); ?></span><span class="stat-note" id="r-batal"></span></div>
                <div class="stat"><span class="stat-label">Total tunai</span><span class="stat-value" id="r-tunai">Rp 0</span></div>
                <div class="stat"><span class="stat-label">Total QRIS</span><span class="stat-value" id="r-qris">Rp 0</span><span class="stat-note">Cocokkan dengan laporan DANA</span></div>
            </div>
            <div class="cards4">
                <div class="stat"><span class="stat-label">Total HPP</span><span class="stat-value" id="r-hpp">Rp 0</span></div>
                <div class="stat"><span class="stat-label">Laba kotor</span><span class="stat-value" id="r-laba">Rp 0</span><span class="stat-note">Pendapatan &minus; HPP</span></div>
                <div class="stat"><span class="stat-label">Rata-rata / transaksi</span><span class="stat-value" id="r-rata">Rp 0</span></div>
                <div class="stat"><span class="stat-label">Pengeluaran</span><span class="stat-value" id="r-keluar">Rp 0</span></div>
            </div>

            <section class="panel">
                <div class="dash-card-head"><h2 class="dash-card-title">Hitungan uang laci per shift</h2></div>
                <div class="table-responsive">
                <table class="tabel" id="tabel-shift">
                    <thead><tr><th>Shift</th><th>Kasir</th><th>Modal awal</th><th>Tunai</th><th>Pengeluaran</th><th>Seharusnya</th><th>Uang fisik</th><th>Selisih</th></tr></thead>
                    <tbody>
                        <?php foreach ($shiftRingkas as $s):
                            $tunai = 0;
                            foreach ($transaksi as $t) {
                                if ($t['shift'] === $s['shift'] && $t['status'] === 'lunas' && $t['metode'] === 'tunai') {
                                    $tunai += $t['total'];
                                }
                            }
                            $seharusnya = $s['modal'] + $tunai - $s['pengeluaran'];
                            $selisih = $s['uang_fisik'] - $seharusnya;
                        ?>
                        <tr data-shift="<?php echo e($s['shift']); ?>" data-kasir="<?php echo e($s['kasir']); ?>" data-keluar="<?php echo $s['pengeluaran']; ?>">
                            <td class="strong"><?php echo e($s['shift']); ?></td>
                            <td><?php echo e($s['kasir']); ?></td>
                            <td><?php echo e(rp($s['modal'])); ?></td>
                            <td><?php echo e(rp($tunai)); ?></td>
                            <td><?php echo e(rp($s['pengeluaran'])); ?><?php echo $s['catatan'] ? ' <span class="meta">(' . e($s['catatan']) . ')</span>' : ''; ?></td>
                            <td><?php echo e(rp($seharusnya)); ?></td>
                            <td class="strong"><?php echo e(rp($s['uang_fisik'])); ?></td>
                            <td>
                                <?php if ($selisih === 0): ?><span class="selisih selisih-ok">Pas</span>
                                <?php elseif ($selisih > 0): ?><span class="selisih selisih-lebih">Lebih <?php echo e(rp($selisih)); ?></span>
                                <?php else: ?><span class="selisih selisih-kurang">Kurang <?php echo e(rp(-$selisih)); ?></span><?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </section>

            <section class="panel">
                <div class="dash-card-head"><h2 class="dash-card-title">Riwayat transaksi</h2></div>
                <div class="table-responsive">
                <table class="tabel" id="tabel-transaksi">
                    <thead><tr><th>Waktu</th><th>No. transaksi</th><th>Kasir</th><th>Shift</th><th>Metode</th><th>Total</th><th>Status</th><th>Struk</th></tr></thead>
                    <tbody>
                        <?php foreach ($transaksi as $t): ?>
                        <tr data-shift="<?php echo e($t['shift']); ?>" data-kasir="<?php echo e($t['kasir']); ?>" data-metode="<?php echo e($t['metode']); ?>"
                            data-status="<?php echo e($t['status']); ?>" data-total="<?php echo $t['total']; ?>" data-hpp="<?php echo $t['hpp']; ?>">
                            <td class="strong"><?php echo e($t['waktu']); ?></td>
                            <td class="mono"><?php echo e($t['kode']); ?></td>
                            <td><?php echo e($t['kasir']); ?></td>
                            <td class="muted"><?php echo e($t['shift']); ?></td>
                            <td><?php echo $t['metode'] === 'qris' ? '<span class="badge badge-qris">QRIS</span>' : 'Tunai'; ?></td>
                            <td class="strong"><?php echo e(rp($t['total'])); ?></td>
                            <td><?php echo $t['status'] === 'lunas' ? '<span class="badge badge-ok">Lunas</span>' : '<span class="badge badge-bad">Dibatalkan</span>'; ?></td>
                            <td><a class="strong" href="../kasir/struk.php?kode=<?php echo urlencode($t['kode']); ?>" target="_blank">Struk</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr id="transaksi-kosong" class="hidden"><td colspan="8" class="empty">Tidak ada transaksi pada filter ini.</td></tr>
                    </tbody>
                </table>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
