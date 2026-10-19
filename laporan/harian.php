<?php
// Laporan harian: gabungan semua shift pada satu tanggal, lengkap dengan HPP dan laba. Khusus pemilik.
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/shift.php';
$page_title = "Laporan Harian";

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');

$stmt = $pdo->prepare("SELECT s.*, u.nama AS kasir FROM shift s JOIN users u ON u.id = s.kasir_id WHERE s.tanggal = :tanggal ORDER BY s.id");
$stmt->execute(['tanggal' => $tanggal]);
$daftarShift = $stmt->fetchAll();

$jumlah = ['tunai' => 0, 'qris' => 0, 'total' => 0, 'jumlah' => 0, 'hpp' => 0, 'pengeluaran' => 0];
$selisihTotal = 0;
$rincian = [];
foreach ($daftarShift as $s) {
    $st = statistik_shift($pdo, (int) $s['id']);
    $selisih = $s['uang_fisik'] !== null ? (int) $s['uang_fisik'] - kas_diharapkan($s, $st) : null;
    $rincian[] = ['shift' => $s, 'stat' => $st, 'selisih' => $selisih];
    foreach (array_keys($jumlah) as $kunci) {
        $jumlah[$kunci] += $st[$kunci];
    }
    $selisihTotal += $selisih ?? 0;
}
$laba = $jumlah['total'] - $jumlah['hpp'];

// Menu terlaris pada tanggal tersebut (hanya transaksi lunas).
$stmt = $pdo->prepare(
    "SELECT i.nama, SUM(i.qty) AS terjual, SUM(i.qty * i.harga) AS omzet, SUM(i.qty * (i.harga - i.hpp)) AS profit
     FROM transaksi_item i
     JOIN transaksi t ON t.id = i.transaksi_id
     JOIN shift s ON s.id = t.shift_id
     WHERE s.tanggal = :tanggal AND t.status = 'lunas'
     GROUP BY i.nama ORDER BY terjual DESC, omzet DESC LIMIT 5"
);
$stmt->execute(['tanggal' => $tanggal]);
$terlaris = $stmt->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Laporan Harian</h1>
                <span class="meta"><?php echo e(tanggal_id($tanggal)); ?></span>
            </div>
        </div>

        <form class="filters" method="get" action="harian.php">
            <div class="field">
                <span class="label">Jenis laporan</span>
                <span class="seg">
                    <a class="seg-opt" href="shift.php?tanggal=<?php echo e($tanggal); ?>">Per shift</a>
                    <a class="seg-opt is-active" href="harian.php?tanggal=<?php echo e($tanggal); ?>">Harian</a>
                </span>
            </div>
            <p class="field">
                <label class="label" for="tanggal">Tanggal</label>
                <input class="input" type="date" id="tanggal" name="tanggal" value="<?php echo e($tanggal); ?>">
            </p>
            <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
        </form>

        <div class="content">
            <div class="cards4">
                <div class="stat stat-primary"><span class="stat-label">Total penjualan</span><span class="stat-value"><?php echo e(rp($jumlah['total'])); ?></span><span class="stat-note"><?php echo $jumlah['jumlah']; ?> transaksi lunas</span></div>
                <div class="stat"><span class="stat-label">Total HPP</span><span class="stat-value"><?php echo e(rp($jumlah['hpp'])); ?></span></div>
                <div class="stat"><span class="stat-label">Laba kotor</span><span class="stat-value"><?php echo e(rp($laba)); ?></span><span class="stat-note">Penjualan &minus; HPP</span></div>
                <div class="stat"><span class="stat-label">Laba setelah pengeluaran</span><span class="stat-value"><?php echo e(rp($laba - $jumlah['pengeluaran'])); ?></span><span class="stat-note">Pengeluaran <?php echo e(rp($jumlah['pengeluaran'])); ?></span></div>
            </div>
            <div class="cards4">
                <div class="stat"><span class="stat-label">Total tunai</span><span class="stat-value"><?php echo e(rp($jumlah['tunai'])); ?></span></div>
                <div class="stat"><span class="stat-label">Total QRIS</span><span class="stat-value"><?php echo e(rp($jumlah['qris'])); ?></span><span class="stat-note">Cocokkan dengan laporan DANA</span></div>
                <div class="stat"><span class="stat-label">Selisih uang laci (semua shift)</span><span class="stat-value stat-value-sm"><?php echo label_selisih($selisihTotal); ?></span></div>
                <div class="stat"><span class="stat-label">Jumlah shift</span><span class="stat-value"><?php echo count($rincian); ?></span></div>
            </div>

            <section class="panel">
                <div class="dash-card-head"><h2 class="dash-card-title">Rincian per shift</h2></div>
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Shift</th><th>Kasir</th><th>Tunai</th><th>QRIS</th><th>Total</th><th>HPP</th><th>Pengeluaran</th><th>Selisih</th></tr></thead>
                    <tbody>
                        <?php foreach ($rincian as $r): ?>
                        <tr>
                            <td><a class="strong" href="shift.php?id=<?php echo (int) $r['shift']['id']; ?>"><?php echo e($r['shift']['nama']); ?></a></td>
                            <td><?php echo e($r['shift']['kasir']); ?></td>
                            <td><?php echo e(rp($r['stat']['tunai'])); ?></td>
                            <td><?php echo e(rp($r['stat']['qris'])); ?></td>
                            <td class="strong"><?php echo e(rp($r['stat']['total'])); ?></td>
                            <td class="muted"><?php echo e(rp($r['stat']['hpp'])); ?></td>
                            <td><?php echo e(rp($r['stat']['pengeluaran'])); ?></td>
                            <td><?php echo $r['selisih'] === null ? '&ndash;' : label_selisih($r['selisih']); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$rincian): ?><tr><td colspan="8" class="empty">Tidak ada shift pada tanggal ini.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>

            <section class="panel">
                <div class="dash-card-head"><h2 class="dash-card-title">Menu terlaris</h2></div>
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Menu</th><th>Terjual</th><th>Omzet</th><th>Profit</th></tr></thead>
                    <tbody>
                        <?php foreach ($terlaris as $m): ?>
                        <tr>
                            <td class="name"><?php echo e($m['nama']); ?></td>
                            <td><?php echo (int) $m['terjual']; ?></td>
                            <td><?php echo e(rp($m['omzet'])); ?></td>
                            <td class="profit"><?php echo e(rp($m['profit'])); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$terlaris): ?><tr><td colspan="4" class="empty">Belum ada penjualan.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
