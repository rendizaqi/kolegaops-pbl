<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
$page_title = "Riwayat Transaksi";

$tanggal = $_GET['tanggal'] ?? date('Y-m-d');
$shiftNama = $_GET['shift'] ?? '';
$metode = $_GET['metode'] ?? '';
$status = $_GET['status'] ?? '';

// Susun filter secara dinamis, tetap memakai prepared statement.
$sql = "SELECT t.*, s.nama AS shift, u.nama AS kasir
        FROM transaksi t
        JOIN shift s ON s.id = t.shift_id
        JOIN users u ON u.id = s.kasir_id
        WHERE DATE(t.dibuat) = :tanggal";
$param = ['tanggal' => $tanggal];
if (in_array($shiftNama, daftar_shift(), true)) {
    $sql .= " AND s.nama = :shift";
    $param['shift'] = $shiftNama;
}
if (in_array($metode, ['tunai', 'qris'], true)) {
    $sql .= " AND t.metode = :metode";
    $param['metode'] = $metode;
}
if (in_array($status, ['lunas', 'open', 'batal'], true)) {
    $sql .= " AND t.status = :status";
    $param['status'] = $status;
}
$sql .= " ORDER BY t.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($param);
$daftar = $stmt->fetchAll();

$totalLunas = 0;
foreach ($daftar as $t) {
    if ($t['status'] === 'lunas') {
        $totalLunas += (int) $t['total'];
    }
}

// Ekspor CSV: sama persis dengan yang tampil di tabel (sebelum ada output HTML).
if (($_GET['ekspor'] ?? '') === 'csv') {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="riwayat-' . $tanggal . '.csv"');
    $out = fopen('php://output', 'w');
    fputcsv($out, ['Waktu', 'No. transaksi', 'Kasir', 'Shift', 'Metode', 'Total', 'Status']);
    foreach ($daftar as $t) {
        fputcsv($out, [jam($t['dibuat']), $t['kode'], $t['kasir'], $t['shift'], $t['metode'], $t['total'], $t['status']]);
    }
    fclose($out);
    exit;
}

$urlCsv = 'riwayat.php?' . http_build_query(['tanggal' => $tanggal, 'shift' => $shiftNama, 'metode' => $metode, 'status' => $status, 'ekspor' => 'csv']);

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Riwayat Transaksi</h1>
                <span class="meta"><?php echo e(tanggal_id($tanggal)); ?> &middot; <?php echo count($daftar); ?> transaksi &middot; lunas <?php echo e(rp($totalLunas)); ?></span>
            </div>
            <a class="btn btn-outline" href="<?php echo e($urlCsv); ?>">Ekspor CSV</a>
        </div>

        <form class="filters" method="get" action="riwayat.php">
            <p class="field">
                <label class="label" for="tanggal">Tanggal</label>
                <input class="input" type="date" id="tanggal" name="tanggal" value="<?php echo e($tanggal); ?>">
            </p>
            <p class="field">
                <label class="label" for="shift">Shift</label>
                <select class="input" id="shift" name="shift">
                    <option value="">Semua</option>
                    <?php foreach (daftar_shift() as $n): ?>
                    <option<?php echo $shiftNama === $n ? ' selected' : ''; ?>><?php echo $n; ?></option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="field">
                <label class="label" for="metode">Metode</label>
                <select class="input" id="metode" name="metode">
                    <option value="">Semua</option>
                    <option value="tunai"<?php echo $metode === 'tunai' ? ' selected' : ''; ?>>Tunai</option>
                    <option value="qris"<?php echo $metode === 'qris' ? ' selected' : ''; ?>>QRIS</option>
                </select>
            </p>
            <p class="field">
                <label class="label" for="status">Status</label>
                <select class="input" id="status" name="status">
                    <option value="">Semua</option>
                    <option value="lunas"<?php echo $status === 'lunas' ? ' selected' : ''; ?>>Lunas</option>
                    <option value="open"<?php echo $status === 'open' ? ' selected' : ''; ?>>Open bill</option>
                    <option value="batal"<?php echo $status === 'batal' ? ' selected' : ''; ?>>Dibatalkan</option>
                </select>
            </p>
            <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
        </form>

        <section class="panel tabel-panel">
            <div class="table-responsive">
            <table class="tabel">
                <thead>
                    <tr><th>Waktu</th><th>No. transaksi</th><th>Kasir</th><th>Shift</th><th>Metode</th><th>Total</th><th>Status</th><th>Struk</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $t): ?>
                    <tr>
                        <td class="strong"><?php echo jam($t['dibuat']); ?></td>
                        <td class="mono"><?php echo e($t['kode']); ?></td>
                        <td><?php echo e($t['kasir']); ?></td>
                        <td class="muted"><?php echo e($t['shift']); ?></td>
                        <td><?php echo $t['status'] === 'open' ? '&ndash;' : ($t['metode'] === 'qris' ? '<span class="badge badge-qris">QRIS</span>' : 'Tunai'); ?></td>
                        <td class="strong"><?php echo e(rp($t['total'])); ?></td>
                        <td>
                            <?php if ($t['status'] === 'lunas'): ?><span class="badge badge-ok">Lunas</span>
                            <?php elseif ($t['status'] === 'open'): ?><span class="badge badge-warn">Open bill</span>
                            <?php else: ?><span class="badge badge-bad">Batal</span><?php endif; ?>
                        </td>
                        <td><a class="strong" href="struk.php?id=<?php echo (int) $t['id']; ?>" target="_blank">Struk</a></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftar): ?>
                    <tr><td colspan="8" class="empty">Tidak ada transaksi pada filter ini.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
