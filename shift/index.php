<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/shift.php';
$page_title = "Shift";

$shift = shift_aktif($pdo);
// Yang boleh mengubah shift berjalan: kasir shift itu sendiri atau pemilik.
$bolehUbah = $shift && ((int) $shift['kasir_id'] === (int) $_SESSION['user_id'] || $loginPemilik);

$statistik = $shift ? statistik_shift($pdo, (int) $shift['id']) : null;
$kas = $shift ? kas_diharapkan($shift, $statistik) : 0;

$pengeluaran = [];
if ($shift) {
    $stmt = $pdo->prepare("SELECT * FROM pengeluaran WHERE shift_id = :id ORDER BY id");
    $stmt->execute(['id' => $shift['id']]);
    $pengeluaran = $stmt->fetchAll();
}

// Modal awal disarankan dari uang fisik di akhir shift terakhir.
$modalSaran = (int) $pdo->query("SELECT COALESCE(uang_fisik, 0) FROM shift WHERE status = 'tutup' ORDER BY id DESC LIMIT 1")->fetchColumn();

$riwayat = $pdo->query(
    "SELECT s.*, u.nama AS kasir FROM shift s JOIN users u ON u.id = s.kasir_id ORDER BY s.id DESC LIMIT 10"
)->fetchAll();

include __DIR__ . '/../includes/header.php';
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
                <form method="post" action="proses_buka.php" class="form-grid">
                    <?php echo csrf_field(); ?>
                    <div class="field">
                        <span class="label">Shift</span>
                        <span class="seg">
                            <?php foreach (daftar_shift() as $i => $nama): ?>
                            <label class="seg-opt radio-seg">
                                <input type="radio" name="nama" value="<?php echo $nama; ?>"<?php echo $i === 0 ? ' checked' : ''; ?>>
                                <span><?php echo $nama; ?></span>
                            </label>
                            <?php endforeach; ?>
                        </span>
                    </div>
                    <p class="field">
                        <label class="label" for="modal">Modal awal di laci (Rp)</label>
                        <input class="input input-strong" type="text" id="modal" name="modal" inputmode="numeric"
                               value="<?php echo $modalSaran ? number_format($modalSaran, 0, ',', '.') : ''; ?>" placeholder="0">
                        <span class="meta"><?php echo $modalSaran ? 'Terisi dari uang akhir shift sebelumnya. Ubah bila berbeda.' : 'Hitung uang tunai yang ada di laci sebelum mulai.'; ?></span>
                    </p>
                    <div class="field">
                        <span class="label">Kasir shift</span>
                        <span class="static-box"><?php echo e($_SESSION['nama']); ?> (akun yang sedang login)</span>
                        <span class="meta">Untuk memakai akun lain, keluar lalu masuk dengan akun tersebut.</span>
                    </div>
                    <button class="btn btn-primary btn-ruang" type="submit"><span>Buka shift &amp; mulai kasir</span><span>&rarr;</span></button>
                </form>
            </section>
            <?php else: ?>
            <div class="cards4">
                <div class="stat stat-primary">
                    <span class="stat-label">Shift <?php echo e($shift['nama']); ?></span>
                    <span class="stat-value stat-value-sm"><?php echo e($shift['kasir']); ?></span>
                    <span class="stat-note">Dibuka <?php echo jam($shift['buka_at']); ?> &middot; modal <?php echo e(rp($shift['modal_awal'])); ?></span>
                </div>
                <div class="stat"><span class="stat-label">Penjualan tunai</span><span class="stat-value"><?php echo e(rp($statistik['tunai'])); ?></span></div>
                <div class="stat"><span class="stat-label">Penjualan QRIS</span><span class="stat-value"><?php echo e(rp($statistik['qris'])); ?></span><span class="stat-note">Masuk ke HP pemilik</span></div>
                <div class="stat"><span class="stat-label">Pengeluaran</span><span class="stat-value"><?php echo e(rp($statistik['pengeluaran'])); ?></span></div>
            </div>

            <div class="two-col">
                <section class="panel box">
                    <h2 class="box-title">Pengeluaran shift ini</h2>
                    <p class="meta">Catat belanja yang memakai uang laci, mis. es batu atau bahan habis.</p>
                    <?php if ($bolehUbah): ?>
                    <form method="post" action="proses_pengeluaran.php" class="row-gap wrap">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="aksi" value="tambah">
                        <input class="input" type="text" name="keterangan" placeholder="Keterangan, mis. Es batu" style="flex:2;min-width:160px" required>
                        <input class="input" type="text" name="nominal" placeholder="Nominal" inputmode="numeric" style="flex:1;min-width:110px" required>
                        <button class="btn btn-outline btn-md" type="submit">Catat</button>
                    </form>
                    <?php endif; ?>
                    <table class="tabel tabel-kecil">
                        <tbody>
                            <?php foreach ($pengeluaran as $k): ?>
                            <tr>
                                <td class="muted"><?php echo jam($k['dibuat']); ?></td>
                                <td class="name"><?php echo e($k['keterangan']); ?></td>
                                <td class="strong"><?php echo e(rp($k['nominal'])); ?></td>
                                <td>
                                    <?php if ($bolehUbah): ?>
                                    <form method="post" action="proses_pengeluaran.php" class="form-hapus" data-pesan="Hapus pengeluaran ini?">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="aksi" value="hapus">
                                        <input type="hidden" name="id" value="<?php echo (int) $k['id']; ?>">
                                        <button class="order-line-remove" type="submit" title="Hapus">&times;</button>
                                    </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                            <?php if (!$pengeluaran): ?>
                            <tr><td colspan="4" class="empty">Belum ada pengeluaran.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </section>

                <section class="panel box">
                    <h2 class="box-title">Tutup shift</h2>
                    <table class="tabel tabel-kecil">
                        <tbody>
                            <tr><td>Modal awal</td><td class="strong kanan"><?php echo e(rp($shift['modal_awal'])); ?></td></tr>
                            <tr><td>+ Penjualan tunai</td><td class="strong kanan"><?php echo e(rp($statistik['tunai'])); ?></td></tr>
                            <tr><td>&minus; Pengeluaran</td><td class="strong kanan"><?php echo e(rp($statistik['pengeluaran'])); ?></td></tr>
                            <tr class="total"><td>Uang tunai seharusnya di laci</td><td class="strong kanan"><?php echo e(rp($kas)); ?></td></tr>
                        </tbody>
                    </table>
                    <?php if ($bolehUbah): ?>
                    <form method="post" action="proses_tutup.php" class="form-grid">
                        <?php echo csrf_field(); ?>
                        <p class="field">
                            <label class="label" for="uang_fisik">Uang tunai di laci sekarang (Rp)</label>
                            <input class="input input-strong" type="text" id="uang_fisik" name="uang_fisik" inputmode="numeric" placeholder="Hitung uang fisik" required>
                        </p>
                        <p class="field">
                            <label class="label" for="catatan">Catatan (opsional)</label>
                            <input class="input" type="text" id="catatan" name="catatan" maxlength="255" placeholder="Mis. kurang Rp 2.000, kembalian pelanggan">
                        </p>
                        <button class="btn btn-primary btn-ruang" type="submit"><span>Tutup shift &amp; lihat laporan</span><span>&rarr;</span></button>
                    </form>
                    <?php else: ?>
                    <p class="alert alert-warn">Hanya <?php echo e($shift['kasir']); ?> atau pemilik yang bisa menutup shift ini.</p>
                    <?php endif; ?>
                </section>
            </div>
            <?php endif; ?>

            <section class="panel">
                <div class="dash-card-head"><h2 class="dash-card-title">Shift terakhir</h2></div>
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Tanggal</th><th>Shift</th><th>Kasir</th><th>Waktu</th><th>Status</th><th>Laporan</th></tr></thead>
                    <tbody>
                        <?php foreach ($riwayat as $r): ?>
                        <tr>
                            <td class="strong"><?php echo e(tanggal_id($r['tanggal'])); ?></td>
                            <td><?php echo e($r['nama']); ?></td>
                            <td><?php echo e($r['kasir']); ?></td>
                            <td class="muted"><?php echo jam($r['buka_at']); ?> &ndash; <?php echo $r['tutup_at'] ? jam($r['tutup_at']) : '...'; ?></td>
                            <td><?php echo $r['status'] === 'buka'
                                ? '<span class="badge badge-status badge-menipis">&#9679; Berjalan</span>'
                                : '<span class="badge badge-ok">Ditutup</span>'; ?></td>
                            <td><a class="strong" href="../laporan/shift.php?id=<?php echo (int) $r['id']; ?>">Lihat &rarr;</a></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (!$riwayat): ?><tr><td colspan="6" class="empty">Belum ada shift.</td></tr><?php endif; ?>
                    </tbody>
                </table>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
