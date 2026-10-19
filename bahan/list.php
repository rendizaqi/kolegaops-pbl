<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
$page_title = "Stok Bahan Baku";

$daftarBahan = $pdo->query("SELECT * FROM bahan ORDER BY id")->fetchAll();

$hitung = ['kritis' => 0, 'menipis' => 0, 'aman' => 0];
foreach ($daftarBahan as $b) {
    $hitung[status_bahan((float) $b['qty'], (float) $b['minimum'])]++;
}

$warnaBar = ['kritis' => 'var(--danger)', 'menipis' => 'var(--warn-line)', 'aman' => 'var(--blue)'];

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Stok Bahan Baku</h1>
                <span class="meta">Dicek per minggu &middot; sisa bahan dilaporkan pegawai tiap shift</span>
            </div>
            <?php if ($pemilik): ?>
            <a class="btn btn-primary" href="tambah.php"><span>+</span><span>Tambah Bahan</span></a>
            <?php endif; ?>
        </div>

        <div class="stok-summary">
            <div><span class="stat-label">Total bahan</span><span class="stok-summary-value"><?php echo count($daftarBahan); ?></span></div>
            <div class="is-warn"><span class="stat-label">Menipis</span><span class="stok-summary-value"><?php echo $hitung['menipis']; ?></span></div>
            <div class="is-danger"><span class="stat-label">Kritis</span><span class="stok-summary-value"><?php echo $hitung['kritis']; ?></span></div>
        </div>

        <section class="panel tabel-panel">
            <div class="table-responsive">
            <table class="tabel">
                <thead>
                    <tr><th>Nama bahan</th><th>Stok saat ini</th><th>Satuan</th><th>Batas min.</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarBahan as $b):
                        $st = status_bahan((float) $b['qty'], (float) $b['minimum']);
                        $persen = min(100, (float) $b['qty'] / max(0.01, (float) $b['minimum'] * 3) * 100);
                    ?>
                    <tr>
                        <td class="name"><?php echo e($b['nama']); ?></td>
                        <td>
                            <span class="stok-level">
                                <span class="strong stok-qty"><?php echo e(angka_id($b['qty'])); ?></span>
                                <span class="stok-bar"><span style="width: <?php echo (int) round($persen); ?>%; background: <?php echo $warnaBar[$st]; ?>"></span></span>
                            </span>
                        </td>
                        <td class="muted"><?php echo e($b['satuan']); ?></td>
                        <td class="muted"><?php echo e(angka_id($b['minimum'])); ?></td>
                        <td>
                            <?php if ($st === 'kritis'): ?><span class="badge badge-status badge-kritis">&#9679; Kritis</span>
                            <?php elseif ($st === 'menipis'): ?><span class="badge badge-status badge-menipis">&#9679; Menipis</span>
                            <?php else: ?><span class="badge badge-status badge-aman">Aman</span><?php endif; ?>
                        </td>
                        <td><a class="btn btn-outline btn-sm" href="update.php?id=<?php echo (int) $b['id']; ?>">Update</a></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
