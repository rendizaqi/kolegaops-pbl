<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
$page_title = "Update Stok Bahan";

$stmt = $pdo->prepare("SELECT * FROM bahan WHERE id = :id");
$stmt->execute(['id' => (int) ($_GET['id'] ?? 0)]);
$bahan = $stmt->fetch();

if (!$bahan) {
    flash('error', 'Bahan tidak ditemukan.');
    header('Location: list.php');
    exit;
}

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Update Stok</h1>
                <span class="meta"><?php echo e($bahan['nama']); ?> &middot; stok saat ini <?php echo e(angka_id($bahan['qty'])); ?> <?php echo e($bahan['satuan']); ?></span>
            </div>
        </div>

        <div class="content">
            <form class="panel box form-grid" method="post" action="proses_update.php">
                <?php echo csrf_field(); ?>
                <input type="hidden" name="id" value="<?php echo (int) $bahan['id']; ?>">
                <p class="field">
                    <label class="label" for="mode">Mode</label>
                    <select class="input" id="mode" name="mode">
                        <option>Tambah</option>
                        <option>Kurangi</option>
                        <option>Set jumlah</option>
                    </select>
                </p>
                <p class="field">
                    <label class="label" for="jumlah">Jumlah (<?php echo e($bahan['satuan']); ?>)</label>
                    <input class="input input-strong" type="text" id="jumlah" name="jumlah" inputmode="decimal" required autofocus>
                </p>
                <p class="row-gap">
                    <button class="btn btn-primary btn-md" type="submit">Simpan</button>
                    <a class="btn btn-plain btn-md" href="list.php">Batal</a>
                </p>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
