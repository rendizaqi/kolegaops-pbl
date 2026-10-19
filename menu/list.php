<?php
$page_title = "Manajemen Menu";
$menu_aktif = "menu";
include __DIR__ . '/../includes/header.php';

$kategori = ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
$jumlahAktif = count(array_filter($menu, function ($m) { return $m['aktif']; }));
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Manajemen Menu</h1>
                <span class="meta"><span id="ringkas-menu"><?php echo count($menu); ?> produk &middot; <?php echo $jumlahAktif; ?> aktif</span> &middot; stok menipis &le; <?php echo STOK_MENU_RENDAH; ?></span>
            </div>
            <a class="btn btn-primary" href="tambah.php"><span>+</span><span>Tambah Produk</span></a>
        </div>

        <div class="toolbar" id="filter-menu">
            <input class="input toolbar-cari" type="text" id="cari" placeholder="Cari produk..." aria-label="Cari produk">
            <select class="input" id="filter-kategori" aria-label="Kategori">
                <option value="">Semua kategori</option>
                <?php foreach ($kategori as $k): ?><option><?php echo e($k); ?></option><?php endforeach; ?>
            </select>
            <select class="input" id="filter-status" aria-label="Status">
                <option value="">Semua status</option>
                <option value="aktif">Aktif</option>
                <option value="nonaktif">Nonaktif</option>
            </select>
        </div>

        <section class="panel tabel-panel">
            <div class="table-responsive">
            <table class="tabel" id="tabel-menu">
                <thead>
                    <tr><th>Nama produk</th><th>Kategori</th><th>HPP</th><th>Harga jual</th><th>Profit</th><th>Stok</th><th>Status</th><th>Aksi</th></tr>
                </thead>
                <tbody>
                    <?php foreach ($menu as $m):
                        $profit = $m['harga'] - $m['hpp'];
                        $margin = round($profit / $m['harga'] * 100);
                    ?>
                    <tr data-nama="<?php echo e(strtolower($m['nama'])); ?>" data-kategori="<?php echo e($m['kategori']); ?>"
                        data-aktif="<?php echo $m['aktif'] ? '1' : '0'; ?>" data-stok="<?php echo $m['stok']; ?>"
                        class="<?php echo $m['aktif'] ? '' : 'row-off'; ?>">
                        <td class="name"><?php echo e($m['nama']); ?></td>
                        <td class="muted"><?php echo e($m['kategori']); ?></td>
                        <td class="muted"><?php echo e(rp($m['hpp'])); ?></td>
                        <td class="strong"><?php echo e(rp($m['harga'])); ?></td>
                        <td class="profit"><?php echo e(rp($profit)); ?><small><?php echo $margin; ?>%</small></td>
                        <td class="sel-stok">
                            <?php if ($m['stok'] <= 0): ?><span class="badge badge-status badge-kritis">Habis</span>
                            <?php elseif ($m['stok'] <= STOK_MENU_RENDAH): ?><span class="badge badge-status badge-menipis">&#9679; Sisa <?php echo $m['stok']; ?></span>
                            <?php else: ?><span class="strong"><?php echo $m['stok']; ?></span><?php endif; ?>
                        </td>
                        <td>
                            <button type="button" class="switch<?php echo $m['aktif'] ? ' is-on' : ''; ?>">
                                <span class="switch-track"><span class="switch-knob"></span></span>
                                <span class="switch-label"><?php echo $m['aktif'] ? 'Aktif' : 'Nonaktif'; ?></span>
                            </button>
                        </td>
                        <td>
                            <div class="row-actions menu-aksi">
                                <span class="restock">
                                    <input class="input input-sm input-jumlah" type="text" value="<?php echo STOK_MENU_ISI_ULANG; ?>" inputmode="numeric" aria-label="Jumlah stok ditambah">
                                    <button type="button" class="btn btn-outline btn-sm btn-tambah-stok">+ Stok</button>
                                </span>
                                <a class="btn btn-outline btn-sm" href="edit.php?id=<?php echo $m['id']; ?>">Edit</a>
                                <button type="button" class="btn btn-danger btn-sm btn-hapus-baris" data-pesan="Hapus menu <?php echo e($m['nama']); ?>?">Hapus</button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <tr id="menu-tidak-ada" class="hidden"><td colspan="8" class="empty">Tidak ada produk.</td></tr>
                </tbody>
            </table>
            </div>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
