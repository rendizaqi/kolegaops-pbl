<?php
$page_title = "Tambah Produk";
$menu_aktif = "menu";
include __DIR__ . '/../includes/header.php';

$kategori = ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
$isi = ['nama' => '', 'kategori' => 'Kopi', 'hpp' => '', 'harga' => '', 'stok' => STOK_MENU_ISI_ULANG, 'aktif' => true];
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Tambah Produk</h1>
                <span class="meta">Isi HPP dan harga jual supaya profit terhitung otomatis.</span>
            </div>
        </div>

        <div class="content">
            <form id="form-menu" class="panel box form-grid" method="get" action="list.php">
                <input type="hidden" name="info" value="tambah">
<?php include __DIR__ . '/_form.php'; ?>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
