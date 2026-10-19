<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
$page_title = "Manajemen Menu";

$kata = trim($_GET['q'] ?? '');
$kategori = $_GET['kategori'] ?? '';
$status = $_GET['status'] ?? '';

$sql = "SELECT * FROM menu WHERE 1 = 1";
$param = [];
if ($kata !== '') {
    $sql .= " AND nama ILIKE :kata";
    $param['kata'] = '%' . $kata . '%';
}
if (in_array($kategori, daftar_kategori(), true)) {
    $sql .= " AND kategori = :kategori";
    $param['kategori'] = $kategori;
}
if ($status === 'aktif') {
    $sql .= " AND aktif = 1";
} elseif ($status === 'nonaktif') {
    $sql .= " AND aktif = 0";
}
$sql .= " ORDER BY id";

$stmt = $pdo->prepare($sql);
$stmt->execute($param);
$daftarMenu = $stmt->fetchAll();

$ringkas = $pdo->query(
    "SELECT COUNT(*) AS semua,
            COUNT(*) FILTER (WHERE aktif = 1) AS aktif,
            COUNT(*) FILTER (WHERE aktif = 1 AND stok <= " . STOK_MENU_RENDAH . ") AS rendah
     FROM menu"
)->fetch();

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Manajemen Menu</h1>
                <span class="meta"><?php echo $ringkas['semua']; ?> produk &middot; <?php echo $ringkas['aktif']; ?> aktif &middot; <?php echo $ringkas['rendah']; ?> stok menipis (&le; <?php echo STOK_MENU_RENDAH; ?>)</span>
            </div>
            <?php if ($pemilik): ?>
            <a class="btn btn-primary" href="tambah.php"><span>+</span><span>Tambah Produk</span></a>
            <?php endif; ?>
        </div>

        <form class="toolbar" method="get" action="list.php">
            <input class="input toolbar-cari" type="text" name="q" value="<?php echo e($kata); ?>" placeholder="Cari produk..." aria-label="Cari produk">
            <select class="input" name="kategori" aria-label="Kategori">
                <option value="">Semua kategori</option>
                <?php foreach (daftar_kategori() as $k): ?>
                <option<?php echo $kategori === $k ? ' selected' : ''; ?>><?php echo e($k); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="input" name="status" aria-label="Status">
                <option value="">Semua status</option>
                <option value="aktif"<?php echo $status === 'aktif' ? ' selected' : ''; ?>>Aktif</option>
                <option value="nonaktif"<?php echo $status === 'nonaktif' ? ' selected' : ''; ?>>Nonaktif</option>
            </select>
            <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
        </form>

        <section class="panel tabel-panel">
            <div class="table-responsive">
            <table class="tabel">
                <thead>
                    <tr>
                        <th>Nama produk</th>
                        <th>Kategori</th>
                        <?php if ($pemilik): ?><th>HPP</th><th>Harga jual</th><th>Profit</th><?php else: ?><th>Harga jual</th><?php endif; ?>
                        <th>Stok</th>
                        <?php if ($pemilik): ?><th>Status</th><?php endif; ?>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftarMenu as $m):
                        $stok = (int) $m['stok'];
                        $profit = (int) $m['harga'] - (int) $m['hpp'];
                        $margin = (int) $m['harga'] > 0 ? round($profit / (int) $m['harga'] * 100) : 0;
                    ?>
                    <tr<?php echo (int) $m['aktif'] ? '' : ' class="row-off"'; ?>>
                        <td class="name"><?php echo e($m['nama']); ?></td>
                        <td class="muted"><?php echo e($m['kategori']); ?></td>
                        <?php if ($pemilik): ?>
                        <td class="muted"><?php echo e(rp($m['hpp'])); ?></td>
                        <td class="strong"><?php echo e(rp($m['harga'])); ?></td>
                        <td class="profit"><?php echo e(rp($profit)); ?><small><?php echo $margin; ?>%</small></td>
                        <?php else: ?>
                        <td class="strong"><?php echo e(rp($m['harga'])); ?></td>
                        <?php endif; ?>
                        <td>
                            <?php if ($stok <= 0): ?><span class="badge badge-status badge-kritis">Habis</span>
                            <?php elseif ($stok <= STOK_MENU_RENDAH): ?><span class="badge badge-status badge-menipis">&#9679; Sisa <?php echo $stok; ?></span>
                            <?php else: ?><span class="strong"><?php echo $stok; ?></span><?php endif; ?>
                        </td>
                        <?php if ($pemilik): ?>
                        <td>
                            <form method="post" action="proses_status.php">
                                <?php echo csrf_field(); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $m['id']; ?>">
                                <button class="switch<?php echo (int) $m['aktif'] ? ' is-on' : ''; ?>" type="submit">
                                    <span class="switch-track"><span class="switch-knob"></span></span><?php echo (int) $m['aktif'] ? 'Aktif' : 'Nonaktif'; ?>
                                </button>
                            </form>
                        </td>
                        <?php endif; ?>
                        <td>
                            <div class="row-actions menu-aksi">
                                <form method="post" action="proses_stok.php" class="restock">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $m['id']; ?>">
                                    <input class="input input-sm" type="text" name="jumlah" value="<?php echo STOK_MENU_ISI_ULANG; ?>" inputmode="numeric" aria-label="Jumlah stok ditambah">
                                    <button class="btn btn-outline btn-sm" type="submit">+ Stok</button>
                                </form>
                                <?php if ($pemilik): ?>
                                <a class="btn btn-outline btn-sm" href="edit.php?id=<?php echo (int) $m['id']; ?>">Edit</a>
                                <form method="post" action="hapus.php" class="form-hapus" data-pesan="Hapus menu <?php echo e($m['nama']); ?>?">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="id" value="<?php echo (int) $m['id']; ?>">
                                    <button class="btn btn-danger btn-sm" type="submit">Hapus</button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (!$daftarMenu): ?>
                    <tr><td colspan="8" class="empty">Tidak ada produk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </section>
<?php include __DIR__ . '/../includes/footer.php'; ?>
