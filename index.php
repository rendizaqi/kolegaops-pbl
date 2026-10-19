<?php
require __DIR__ . '/includes/auth.php';

// Dashboard khusus pemilik; pegawai langsung diarahkan ke kasir.
if (!$loginPemilik) {
    header('Location: kasir/index.php');
    exit;
}

require __DIR__ . '/includes/koneksi.php';
require __DIR__ . '/includes/helpers.php';
$page_title = "Dashboard";

$hariIni = date('Y-m-d');

// Penjualan, jumlah transaksi, dan HPP hari ini (hanya yang lunas).
$stmt = $pdo->prepare(
    "SELECT COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS total, COALESCE(SUM(hpp), 0) AS hpp
     FROM transaksi WHERE DATE(dibuat) = :hari AND status = 'lunas'"
);
$stmt->execute(['hari' => $hariIni]);
$hari = $stmt->fetch();
$penjualan = (int) $hari['total'];
$jumlah = (int) $hari['jumlah'];
$laba = $penjualan - (int) $hari['hpp'];
$rata = $jumlah > 0 ? $penjualan / $jumlah : 0;

$stmt = $pdo->prepare(
    "SELECT COALESCE(SUM(p.nominal), 0) FROM pengeluaran p JOIN shift s ON s.id = p.shift_id WHERE s.tanggal = :hari"
);
$stmt->execute(['hari' => $hariIni]);
$pengeluaran = (int) $stmt->fetchColumn();

// Menu yang stoknya tinggal sedikit.
$stmt = $pdo->prepare("SELECT nama, stok FROM menu WHERE aktif = 1 AND stok <= :batas ORDER BY stok, nama");
$stmt->execute(['batas' => STOK_MENU_RENDAH]);
$menuRendah = $stmt->fetchAll();

// Bahan baku berstatus kritis.
$kritis = 0;
foreach ($pdo->query("SELECT qty, minimum FROM bahan")->fetchAll() as $bahan) {
    if (status_bahan((float) $bahan['qty'], (float) $bahan['minimum']) === 'kritis') {
        $kritis++;
    }
}

// Grafik: 6 hari sebelumnya dari ringkasan_harian + hari ini dari transaksi.
$stmt = $pdo->prepare("SELECT tanggal, total FROM ringkasan_harian WHERE tanggal < :hari ORDER BY tanggal DESC LIMIT 6");
$stmt->execute(['hari' => $hariIni]);
$grafik = [];
foreach (array_reverse($stmt->fetchAll()) as $baris) {
    $grafik[] = ['hari' => hari_singkat($baris['tanggal']), 'total' => (int) $baris['total']];
}
$grafik[] = ['hari' => hari_singkat($hariIni), 'total' => $penjualan];
$totalMinggu = array_sum(array_column($grafik, 'total'));
$tertinggi = max(1, ...array_column($grafik, 'total'));

$terbaru = $pdo->query(
    "SELECT t.*, u.nama AS kasir FROM transaksi t
     JOIN shift s ON s.id = t.shift_id JOIN users u ON u.id = s.kasir_id
     WHERE t.status <> 'open' ORDER BY t.id DESC LIMIT 8"
)->fetchAll();

include __DIR__ . '/includes/header.php';
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
                <span class="stat-note"><?php echo $jumlah; ?> transaksi &middot; rata-rata <?php echo e(rp($rata)); ?></span>
            </div>
            <div class="stat">
                <span class="stat-label">Laba kotor</span>
                <span class="stat-value"><?php echo e(rp($laba)); ?></span>
                <span class="stat-note">Penjualan &minus; HPP &middot; pengeluaran <?php echo e(rp($pengeluaran)); ?></span>
            </div>
            <div class="stat<?php echo $menuRendah ? ' stat-warn' : ''; ?>">
                <span class="stat-label">Stok menu menipis</span>
                <span class="stat-value"><?php echo count($menuRendah); ?> menu</span>
                <a href="menu/list.php" class="stat-link">Isi ulang stok &rarr;</a>
            </div>
            <div class="stat stat-danger">
                <span class="stat-label">Bahan baku kritis</span>
                <span class="stat-value"><?php echo $kritis; ?> bahan</span>
                <a href="bahan/list.php" class="stat-link">Lihat stok &rarr;</a>
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
                    <a href="transaksi/riwayat.php" class="strong">Semua &rarr;</a>
                </div>
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Waktu</th><th>Kasir</th><th>Total</th><th>Status</th></tr></thead>
                    <tbody>
                        <?php foreach ($terbaru as $t): ?>
                        <tr>
                            <td class="strong"><?php echo jam($t['dibuat']); ?></td>
                            <td><?php echo e($t['kasir']); ?></td>
                            <td class="strong"><?php echo e(rp($t['total'])); ?></td>
                            <td><?php echo $t['status'] === 'lunas'
                                ? '<span class="badge badge-ok badge-xs">Lunas</span>'
                                : '<span class="badge badge-bad badge-xs">Batal</span>'; ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$terbaru): ?>
                        <tr><td colspan="4" class="empty">Belum ada transaksi.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/includes/footer.php'; ?>
