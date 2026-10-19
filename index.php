<?php
$page_title = "Dashboard";
$menu_aktif = "dashboard";
include __DIR__ . '/includes/header.php';

// Ringkasan hari ini dihitung dari data contoh transaksi.
$penjualan = 0;
$hppTotal = 0;
$jumlahLunas = 0;
foreach ($transaksi as $t) {
    if ($t['status'] === 'lunas') {
        $penjualan += $t['total'];
        $hppTotal += $t['hpp'];
        $jumlahLunas++;
    }
}
$laba = $penjualan - $hppTotal;
$rata = $jumlahLunas > 0 ? $penjualan / $jumlahLunas : 0;

$pengeluaran = array_sum(array_column($shiftRingkas, 'pengeluaran'));

$menuRendah = array_filter($menu, function ($m) {
    return $m['aktif'] && $m['stok'] <= STOK_MENU_RENDAH;
});

$bahanKritis = 0;
foreach ($bahan as $b) {
    if (status_bahan($b['qty'], $b['minimum']) === 'kritis') {
        $bahanKritis++;
    }
}

// Grafik 7 hari: 6 hari sebelumnya + hari ini.
$grafik = [];
foreach ($penjualanEnamHari as $i => $total) {
    $grafik[] = ['hari' => ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][(int) date('w', strtotime('-' . (6 - $i) . ' days'))], 'total' => $total];
}
$grafik[] = ['hari' => ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][(int) date('w')], 'total' => $penjualan];
$totalMinggu = array_sum(array_column($grafik, 'total'));
$tertinggi = max(array_column($grafik, 'total'));

$terbaru = array_slice(array_reverse($transaksi), 0, 8);
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Dashboard</h1>
                <span class="meta">Ringkasan hari ini &middot; <?php echo e(tanggal_id()); ?></span>
            </div>
            <span class="meta">Diperbarui <?php echo date('H:i'); ?></span>
        </div>

        <div class="stats stats4">
            <div class="stat stat-primary">
                <span class="stat-label">Total penjualan</span>
                <span class="stat-value"><?php echo e(rp($penjualan)); ?></span>
                <span class="stat-note"><?php echo $jumlahLunas; ?> transaksi &middot; rata-rata <?php echo e(rp($rata)); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Laba kotor</span>
                <span class="stat-value"><?php echo e(rp($laba)); ?></span>
                <span class="stat-note">Penjualan &minus; HPP &middot; pengeluaran <?php echo e(rp($pengeluaran)); ?></span>
            </div>
            <div class="stat stat-warn">
                <span class="stat-label">Stok menu menipis</span>
                <span class="stat-value"><?php echo count($menuRendah); ?> menu</span>
                <a href="menu/list.php" class="stat-link">Isi ulang stok &rarr;</a>
            </div>
            <div class="stat stat-danger">
                <span class="stat-label">Bahan baku kritis</span>
                <span class="stat-value"><?php echo $bahanKritis; ?> bahan</span>
                <a href="stok/list.php" class="stat-link">Lihat stok &rarr;</a>
            </div>
        </div>

        <div class="dash-grid">
            <section class="panel dash-card">
                <div class="dash-card-head">
                    <h2 class="dash-card-title">Pendapatan 7 hari terakhir</h2>
                    <span class="meta">Total <?php echo e(rp($totalMinggu)); ?></span>
                </div>
                <div class="chart">
                    <?php foreach ($grafik as $i => $batang): ?>
                    <div class="chart-col" title="<?php echo e($batang['hari'] . ': ' . rp($batang['total'])); ?>">
                        <span class="chart-value"><?php echo number_format($batang['total'] / 1000000, 1, ',', '.'); ?>jt</span>
                        <div class="chart-bar-area">
                            <div class="chart-bar<?php echo $i === count($grafik) - 1 ? ' is-today' : ''; ?>"
                                 style="height: <?php echo (int) round($batang['total'] / $tertinggi * 100); ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <div class="chart-days">
                    <?php foreach ($grafik as $batang): ?><span><?php echo e($batang['hari']); ?></span><?php endforeach; ?>
                </div>
            </section>

            <section class="panel dash-card">
                <div class="dash-card-head">
                    <h2 class="dash-card-title">Transaksi terbaru</h2>
                    <a href="laporan/harian.php" class="strong">Semua &rarr;</a>
                </div>
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Waktu</th><th>Kasir</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($terbaru as $t): ?>
                        <tr>
                            <td class="strong"><?php echo e($t['waktu']); ?></td>
                            <td><?php echo e($t['kasir']); ?></td>
                            <td class="strong"><?php echo e(rp($t['total'])); ?></td>
                            <td><?php echo $t['status'] === 'lunas'
                                ? '<span class="badge badge-ok badge-xs">Lunas</span>'
                                : '<span class="badge badge-bad badge-xs">Batal</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/includes/footer.php'; ?>
