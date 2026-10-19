<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
$page_title = "Edit Produk";

$stmt = $pdo->prepare("SELECT * FROM menu WHERE id = :id");
$stmt->execute(['id' => (int) ($_GET['id'] ?? 0)]);
$menu = $stmt->fetch();

if (!$menu) {
    flash('error', 'Menu tidak ditemukan.');
    header('Location: list.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Edit Produk</h1>
                <span class="meta"><?php echo e($menu['nama']); ?></span>
            </div>
        </div>

        <div class="content">
            <form id="form-menu" class="panel box form-grid" method="post" action="proses_edit.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int) $menu['id']; ?>">
                <p class="field">
                    <label class="label" for="nama">Nama produk</label>
                    <input class="input" type="text" id="nama" name="nama" value="<?php echo e($menu['nama']); ?>" required>
                </p>
                <p class="field">
                    <label class="label" for="kategori">Kategori</label>
                    <select class="input" id="kategori" name="kategori">
                        <?php foreach (daftar_kategori() as $k): ?>
                        <option<?php echo $menu['kategori'] === $k ? ' selected' : ''; ?>><?php echo e($k); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <div class="row-gap">
                    <p class="field grow">
                        <label class="label" for="hpp">HPP (Rp)</label>
                        <input class="input" type="text" id="hpp" name="hpp" inputmode="numeric" value="<?php echo e($menu['hpp']); ?>">
                    </p>
                    <p class="field grow">
                        <label class="label" for="harga">Harga jual (Rp)</label>
                        <input class="input" type="text" id="harga" name="harga" inputmode="numeric" value="<?php echo e($menu['harga']); ?>" required>
                    </p>
                </div>
                <p class="field">
                    <label class="label" for="stok">Stok siap jual</label>
                    <input class="input" type="number" id="stok" name="stok" min="0" value="<?php echo (int) $menu['stok']; ?>" required>
                </p>
                <p class="check">
                    <input type="checkbox" id="aktif" name="aktif" value="1"<?php echo (int) $menu['aktif'] ? ' checked' : ''; ?>>
                    <label for="aktif">Aktif (tampil di kasir)</label>
                </p>
                <p class="row-gap">
                    <button class="btn btn-primary btn-md" type="submit">Simpan perubahan</button>
                    <a class="btn btn-plain btn-md" href="list.php">Batal</a>
                </p>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
