<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
$page_title = "Tambah Bahan";
include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Tambah Bahan</h1>
                <span class="meta">Tambah bahan baku baru ke daftar stok.</span>
            </div>
        </div>

        <div class="content">
            <form class="panel box form-grid" method="post" action="proses_tambah.php">
                <?php echo csrf_field(); ?>
                <p class="field">
                    <label class="label" for="nama">Nama bahan</label>
                    <input class="input" type="text" id="nama" name="nama" required>
                </p>
                <p class="field">
                    <label class="label" for="qty">Stok saat ini</label>
                    <input class="input" type="text" id="qty" name="qty" inputmode="decimal" value="0">
                </p>
                <p class="field">
                    <label class="label" for="satuan">Satuan</label>
                    <input class="input" type="text" id="satuan" name="satuan" placeholder="kg / liter / pcs" required>
                </p>
                <p class="field">
                    <label class="label" for="minimum">Batas minimum</label>
                    <input class="input" type="text" id="minimum" name="minimum" inputmode="decimal" value="0">
                </p>
                <p class="row-gap">
                    <button class="btn btn-primary btn-md" type="submit">Simpan</button>
                    <a class="btn btn-plain btn-md" href="list.php">Batal</a>
                </p>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
