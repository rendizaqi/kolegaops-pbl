<?php
$page_title = "Manajemen Stok";
$menu_aktif = "stok";
include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Manajemen Stok</h1>
                <span class="meta">Bahan baku &middot; dicek per minggu &middot; sisa dilaporkan tiap shift</span>
            </div>
            <button type="button" class="btn btn-primary" id="btn-tambah-bahan"><span>+</span><span>Tambah Bahan</span></button>
        </div>

        <div class="stok-summary">
            <div><span class="stat-label">Total bahan</span><span class="stok-summary-value" id="jml-total"><?php echo count($bahan); ?></span></div>
            <div class="is-warn"><span class="stat-label">Menipis</span><span class="stok-summary-value" id="jml-menipis">0</span></div>
            <div class="is-danger"><span class="stat-label">Kritis</span><span class="stok-summary-value" id="jml-kritis">0</span></div>
        </div>

        <section class="panel tabel-panel">
            <div class="table-responsive">
            <table class="tabel" id="tabel-stok">
                <thead>
                    <tr><th>Nama bahan</th><th>Stok saat ini</th><th>Satuan</th><th>Batas min.</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($bahan as $b):
                        $st = status_bahan($b['qty'], $b['minimum']);
                        $persen = min(100, $b['qty'] / max(0.01, $b['minimum'] * 3) * 100);
                        $warna = ['kritis' => 'var(--danger)', 'menipis' => 'var(--warn-line)', 'aman' => 'var(--blue)'][$st];
                    ?>
                    <tr data-nama="<?php echo e($b['nama']); ?>" data-qty="<?php echo $b['qty']; ?>" data-min="<?php echo $b['minimum']; ?>" data-satuan="<?php echo e($b['satuan']); ?>">
                        <td class="name"><?php echo e($b['nama']); ?></td>
                        <td class="sel-qty">
                            <span class="stok-level">
                                <span class="strong stok-qty"><?php echo e(angka_id($b['qty'])); ?></span>
                                <span class="stok-bar"><span style="width: <?php echo (int) round($persen); ?>%; background: <?php echo $warna; ?>"></span></span>
                            </span>
                        </td>
                        <td class="muted"><?php echo e($b['satuan']); ?></td>
                        <td class="muted"><?php echo e(angka_id($b['minimum'])); ?></td>
                        <td class="sel-status">
                            <?php if ($st === 'kritis'): ?><span class="badge badge-status badge-kritis">&#9679; Kritis</span>
                            <?php elseif ($st === 'menipis'): ?><span class="badge badge-status badge-menipis">&#9679; Menipis</span>
                            <?php else: ?><span class="badge badge-status badge-aman">Aman</span><?php endif; ?>
                        </td>
                        <td><button type="button" class="btn btn-outline btn-sm btn-update-stok">Update</button></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            </div>
        </section>

        <!-- Dialog update stok -->
        <div class="dialog-backdrop hidden" id="dialog-update">
            <form class="dialog" id="form-update" role="dialog" aria-modal="true" aria-labelledby="judul-update">
                <div class="dialog-head">
                    <h2 class="dialog-title" id="judul-update">Update Stok</h2>
                    <span class="meta" id="info-update"></span>
                </div>
                <div class="dialog-body">
                    <div class="mode-picker" id="mode-picker">
                        <button type="button" class="chip is-active" data-mode="tambah">Tambah</button>
                        <button type="button" class="chip" data-mode="kurangi">Kurangi</button>
                        <button type="button" class="chip" data-mode="set">Set jumlah</button>
                    </div>
                    <p class="field">
                        <label class="label" for="jumlah-update" id="label-jumlah">Jumlah</label>
                        <input class="input input-strong" type="text" id="jumlah-update" inputmode="decimal" required>
                    </p>
                    <p class="field">
                        <label class="label" for="catatan-update">Catatan (opsional)</label>
                        <input class="input" type="text" id="catatan-update" placeholder="Mis. belanja pasar pagi">
                    </p>
                </div>
                <div class="dialog-actions">
                    <button type="button" class="btn btn-plain btn-md btn-tutup-dialog">Batal</button>
                    <button type="submit" class="btn btn-primary btn-md">Simpan</button>
                </div>
            </form>
        </div>

        <!-- Dialog tambah bahan -->
        <div class="dialog-backdrop hidden" id="dialog-bahan">
            <form class="dialog" id="form-bahan" role="dialog" aria-modal="true" aria-labelledby="judul-bahan">
                <div class="dialog-head">
                    <h2 class="dialog-title" id="judul-bahan">Tambah Bahan</h2>
                    <span class="meta">Tambah bahan baku baru ke daftar stok.</span>
                </div>
                <div class="dialog-body">
                    <p class="field"><label class="label" for="bahan-nama">Nama bahan</label><input class="input" type="text" id="bahan-nama" required></p>
                    <p class="field"><label class="label" for="bahan-qty">Stok saat ini</label><input class="input" type="text" id="bahan-qty" inputmode="decimal" value="0"></p>
                    <p class="field"><label class="label" for="bahan-satuan">Satuan</label><input class="input" type="text" id="bahan-satuan" placeholder="kg / liter / pcs" required></p>
                    <p class="field"><label class="label" for="bahan-min">Batas minimum</label><input class="input" type="text" id="bahan-min" inputmode="decimal" value="0"></p>
                </div>
                <div class="dialog-actions">
                    <button type="button" class="btn btn-plain btn-md btn-tutup-dialog">Batal</button>
                    <button type="submit" class="btn btn-primary btn-md">Simpan</button>
                </div>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
