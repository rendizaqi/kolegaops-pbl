<?php
$page_title = "Kasir";
$menu_aktif = "kasir";
$pengguna = ['nama' => 'Rina', 'peran' => 'Kasir · Shift Pagi'];
include __DIR__ . '/../includes/header.php';

$kategori = ['Semua', 'Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
$menuAktif = array_filter($menu, function ($m) {
    return $m['aktif'];
});
?>
        <div class="kasir" id="halaman-kasir">
            <section class="kasir-menu">
                <div class="page-header">
                    <div class="page-heading">
                        <h1 class="page-title">Kasir</h1>
                        <span class="meta">Shift Pagi &middot; Kasir: <strong>Rina</strong></span>
                    </div>
                    <div class="kasir-header-right">
                        <input class="input kasir-search" type="text" id="cari-menu" placeholder="Cari menu..." aria-label="Cari menu">
                        <span class="meta"><?php echo e(tanggal_id()); ?></span>
                    </div>
                </div>

                <div class="kasir-cats">
                    <?php foreach ($kategori as $i => $k): ?>
                    <button type="button" class="chip<?php echo $i === 0 ? ' is-active' : ''; ?>" data-kategori="<?php echo e($k); ?>"><?php echo e($k); ?></button>
                    <?php endforeach; ?>
                </div>

                <div class="kasir-grid" id="kasir-grid">
                    <?php foreach ($menuAktif as $m): ?>
                    <button type="button" class="menu-card" data-id="<?php echo $m['id']; ?>" data-nama="<?php echo e($m['nama']); ?>"
                            data-kategori="<?php echo e($m['kategori']); ?>" data-harga="<?php echo $m['harga']; ?>" data-stok="<?php echo $m['stok']; ?>">
                        <span class="photo menu-card-photo">foto</span>
                        <span class="menu-card-name"><?php echo e($m['nama']); ?></span>
                        <span class="menu-card-foot">
                            <span class="menu-card-price"><?php echo e(rp($m['harga'])); ?></span>
                            <span class="menu-card-plus">+</span>
                        </span>
                        <span class="stok-tag">Stok <?php echo $m['stok']; ?></span>
                    </button>
                    <?php endforeach; ?>
                    <p class="empty hidden" id="menu-kosong">Tidak ada menu yang cocok.</p>
                </div>
            </section>

            <aside class="order">
                <div class="order-head">
                    <h2 class="order-title">Pesanan</h2>
                    <span class="meta">#TRX-0114 &middot; Meja 4</span>
                </div>

                <div class="order-lines">
                    <p class="alert alert-ok hidden" id="pesan-sukses"></p>
                    <p class="alert alert-err hidden" id="pesan-error"></p>
                    <p class="empty" id="pesanan-kosong">Belum ada item. Klik menu di kiri untuk menambahkan.</p>
                    <div id="daftar-pesanan"></div>
                </div>

                <div class="order-foot">
                    <div class="order-subtotal">
                        <span>Subtotal (<span id="jumlah-item">0</span> item)</span>
                        <span id="subtotal">Rp 0</span>
                    </div>
                    <div class="order-total">
                        <span class="order-total-label">TOTAL</span>
                        <span class="order-total-value" id="total">Rp 0</span>
                    </div>

                    <div class="pay-methods">
                        <button type="button" class="seg-opt is-active" data-metode="tunai">Tunai</button>
                        <button type="button" class="seg-opt" data-metode="qris">QRIS</button>
                    </div>

                    <div id="panel-qris" class="qris-box hidden">
                        <div class="qris-code" aria-hidden="true"></div>
                        <div class="qris-info">
                            <span class="label">Total yang dibayar via QRIS</span>
                            <span class="qris-total" id="total-qris">Rp 0</span>
                            <span class="meta">Minta pelanggan membayar sesuai nominal ini, lalu cek notifikasi masuk sebelum menekan Proses Bayar.</span>
                        </div>
                    </div>

                    <div id="panel-tunai">
                        <p class="field">
                            <label class="label" for="input-bayar">Nominal bayar</label>
                            <input class="input input-strong" type="text" id="input-bayar" inputmode="numeric" placeholder="0">
                        </p>
                        <div class="quick-cash">
                            <button type="button" data-bayar="pas">Uang pas</button>
                            <button type="button" data-bayar="20000">20.000</button>
                            <button type="button" data-bayar="50000">50.000</button>
                            <button type="button" data-bayar="100000">100.000</button>
                        </div>
                        <div class="kembalian">
                            <span class="kembalian-label" id="label-kembalian">Kembalian</span>
                            <span class="kembalian-value" id="nilai-kembalian">Rp 0</span>
                        </div>
                    </div>

                    <button type="button" class="btn btn-primary pay-btn" id="btn-bayar" disabled>
                        <span>Proses Bayar</span><span>&rarr;</span>
                    </button>
                    <div class="row-gap">
                        <a class="btn btn-outline btn-md grow btn-tengah" href="struk.php" target="_blank">Lihat contoh struk</a>
                        <button type="button" class="btn btn-plain btn-md" id="btn-kosongkan" disabled>Kosongkan</button>
                    </div>
                </div>
            </aside>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
