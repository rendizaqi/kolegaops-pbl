<?php
$page_title = "Edit Produk";
$menu_aktif = "menu";
include __DIR__ . '/../includes/header.php';

$kategori = ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];

// Cari menu yang diedit dari data contoh (nanti: query database berdasarkan id).
$isi = null;
foreach ($menu as $m) {
    if ($m['id'] === (int) ($_GET['id'] ?? 0)) {
        $isi = $m;
    }
}
if (!$isi) {
    header('Location: list.php');
    exit;
}
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Edit Produk</h1>
                <span class="meta"><?php echo e($isi['nama']); ?></span>
            </div>
        </div>

        <div class="content">
            <form id="form-menu" class="panel box form-grid" method="get" action="list.php">
                <input type="hidden" name="info" value="edit">
<?php include __DIR__ . '/_form.php'; ?>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
