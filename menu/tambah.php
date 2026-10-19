<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
$page_title = "Tambah Produk";
include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Tambah Produk</h1>
                <span class="meta">Isi HPP dan harga jual supaya profit terhitung otomatis.</span>
            </div>
        </div>

        <div class="content">
            <form id="form-menu" class="panel box form-grid" method="post" action="proses_tambah.php">
                <?php echo csrf_field(); ?>
                <p class="field">
                    <label class="label" for="nama">Nama produk</label>
                    <input class="input" type="text" id="nama" name="nama" required>
                </p>
                <p class="field">
                    <label class="label" for="kategori">Kategori</label>
                    <select class="input" id="kategori" name="kategori">
                        <?php foreach (daftar_kategori() as $k): ?>
                        <option><?php echo e($k); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <div class="row-gap">
                    <p class="field grow">
                        <label class="label" for="hpp">HPP (Rp)</label>
                        <input class="input" type="text" id="hpp" name="hpp" inputmode="numeric" placeholder="Modal per porsi">
                    </p>
                    <p class="field grow">
                        <label class="label" for="harga">Harga jual (Rp)</label>
                        <input class="input" type="text" id="harga" name="harga" inputmode="numeric" required>
                    </p>
                </div>
                <p class="field">
                    <label class="label" for="stok">Stok siap jual</label>
                    <input class="input" type="number" id="stok" name="stok" min="0" value="<?php echo STOK_MENU_ISI_ULANG; ?>" required>
                </p>
                <p class="check">
                    <input type="checkbox" id="aktif" name="aktif" value="1" checked>
                    <label for="aktif">Aktif (tampil di kasir)</label>
                </p>
                <p class="row-gap">
                    <button class="btn btn-primary btn-md" type="submit">Simpan</button>
                    <a class="btn btn-plain btn-md" href="list.php">Batal</a>
                </p>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
