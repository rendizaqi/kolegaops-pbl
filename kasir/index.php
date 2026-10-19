<?php
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/shift.php';
$page_title = "Kasir";

$shift = shift_aktif($pdo);
$bolehJual = $shift && (int) $shift['kasir_id'] === (int) $_SESSION['user_id'];
$nonaktif = $bolehJual ? '' : ' disabled';

// Keadaan kasir disimpan di session: isi keranjang [menu_id => jumlah], metode bayar, nominal bayar, meja.
$keranjang = $_SESSION['keranjang'] ?? [];
$metode = ($_SESSION['metode'] ?? 'tunai') === 'qris' ? 'qris' : 'tunai';
$meja = $_SESSION['meja'] ?? 'Meja 1';

$kategori = $_GET['cat'] ?? 'Semua';
$kata = trim($_GET['q'] ?? '');

// Semua menu (untuk harga & stok), lalu disaring untuk tampilan kartu.
$semuaMenu = [];
foreach ($pdo->query("SELECT * FROM menu ORDER BY id")->fetchAll() as $m) {
    $semuaMenu[(int) $m['id']] = $m;
}
$kartu = array_filter($semuaMenu, function ($m) use ($kategori, $kata) {
    return (int) $m['aktif'] === 1
        && ($kategori === 'Semua' || $m['kategori'] === $kategori)
        && ($kata === '' || stripos($m['nama'], $kata) !== false);
});

// Baris pesanan, total, jumlah item.
$baris = [];
$total = 0;
$jumlahItem = 0;
foreach ($keranjang as $id => $qty) {
    if (!isset($semuaMenu[$id])) {
        continue;
    }
    $m = $semuaMenu[$id];
    $subtotal = (int) $m['harga'] * $qty;
    $total += $subtotal;
    $jumlahItem += $qty;
    $baris[] = ['id' => $id, 'nama' => $m['nama'], 'harga' => (int) $m['harga'], 'qty' => $qty, 'subtotal' => $subtotal];
}

$bayar = $metode === 'qris' ? $total : (int) ($_SESSION['bayar'] ?? 0);
$kurang = $metode === 'tunai' && $bayar > 0 && $bayar < $total;
$bisaBayar = $bolehJual && $total > 0 && $bayar >= $total;

$openBill = $pdo->query("SELECT * FROM transaksi WHERE status = 'open' ORDER BY id")->fetchAll();
$strukTerakhir = $_SESSION['struk_terakhir'] ?? null;
$nomorBerikut = (int) $pdo->query("SELECT COALESCE(MAX(id), 0) + 1 FROM transaksi")->fetchColumn();

// Token CSRF + posisi filter, disisipkan di setiap form kasir supaya setelah
// aksi pengguna kembali ke kategori / pencarian yang sama.
function bidang_kasir($kategori, $kata)
{
    return csrf_field()
        . '<input type="hidden" name="cat" value="' . e($kategori) . '">'
        . '<input type="hidden" name="q" value="' . e($kata) . '">';
}

include __DIR__ . '/../includes/header.php';
?>
        <div class="kasir">
            <section class="kasir-menu">
                <div class="page-header">
                    <div class="page-heading">
                        <h1 class="page-title">Kasir</h1>
                        <span class="meta">
                            <?php if ($shift): ?>Shift <?php echo e($shift['nama']); ?> &middot; Kasir: <strong><?php echo e($shift['kasir']); ?></strong>
                            <?php else: ?>Belum ada shift aktif<?php endif; ?>
                        </span>
                    </div>
                    <div class="kasir-header-right">
                        <form method="get" action="index.php">
                            <input type="hidden" name="cat" value="<?php echo e($kategori); ?>">
                            <input class="input kasir-search" type="text" name="q" value="<?php echo e($kata); ?>" placeholder="Cari menu... (Enter)">
                        </form>
                        <span class="meta"><?php echo e(tanggal_id()); ?></span>
                    </div>
                </div>

                <?php if (!$shift): ?>
                <div class="gate">
                    <h2>Belum ada shift yang berjalan</h2>
                    <p>Buka shift dulu untuk mulai mencatat transaksi. Kasir shift adalah akun yang membuka shift.</p>
                    <a class="btn btn-primary" href="../shift/index.php"><span>Buka shift</span><span>&rarr;</span></a>
                </div>
                <?php else: ?>
                    <?php if (!$bolehJual): ?>
                    <p class="alert alert-warn">
                        Shift ini dibuka oleh <strong><?php echo e($shift['kasir']); ?></strong>, jadi hanya akun itu yang bisa mencatat transaksi.
                        Untuk ganti kasir: tutup shift di menu <a href="../shift/index.php">Shift</a>, lalu login dengan akun kasir berikutnya.
                    </p>
                    <?php endif; ?>

                    <div class="kasir-cats">
                        <?php foreach (array_merge(['Semua'], daftar_kategori()) as $k): ?>
                        <a class="chip<?php echo $kategori === $k ? ' is-active' : ''; ?>"
                           href="index.php?cat=<?php echo urlencode($k); ?><?php echo $kata !== '' ? '&q=' . urlencode($kata) : ''; ?>"><?php echo e($k); ?></a>
                        <?php endforeach; ?>
                    </div>

                    <div class="kasir-grid">
                        <?php foreach ($kartu as $m): $stok = (int) $m['stok']; ?>
                        <form method="post" action="proses_keranjang.php" class="card-form">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <input type="hidden" name="aksi" value="tambah">
                            <input type="hidden" name="id" value="<?php echo (int) $m['id']; ?>">
                            <button class="menu-card" type="submit"<?php echo ($stok <= 0 || !$bolehJual) ? ' disabled' : ''; ?>>
                                <span class="photo menu-card-photo">foto</span>
                                <span class="menu-card-name"><?php echo e($m['nama']); ?></span>
                                <span class="menu-card-foot">
                                    <span class="menu-card-price"><?php echo e(rp($m['harga'])); ?></span>
                                    <span class="menu-card-plus">+</span>
                                </span>
                                <?php if ($stok <= 0): ?>
                                    <span class="stok-tag stok-habis">Stok habis</span>
                                <?php elseif ($stok <= STOK_MENU_RENDAH): ?>
                                    <span class="stok-tag stok-rendah">Sisa <?php echo $stok; ?></span>
                                <?php else: ?>
                                    <span class="stok-tag">Stok <?php echo $stok; ?></span>
                                <?php endif; ?>
                            </button>
                        </form>
                        <?php endforeach; ?>
                        <?php if (!$kartu): ?><p class="empty">Tidak ada menu yang cocok.</p><?php endif; ?>
                    </div>
                <?php endif; ?>
            </section>

            <aside class="order">
                <div class="order-head">
                    <h2 class="order-title">Pesanan</h2>
                    <span class="meta">#TRX-<?php echo str_pad($nomorBerikut, 4, '0', STR_PAD_LEFT); ?></span>
                </div>

                <form method="post" action="proses_keranjang.php" class="meja-form">
                    <?php echo bidang_kasir($kategori, $kata); ?>
                    <input type="hidden" name="aksi" value="meja">
                    <label class="label" for="meja">Meja / nama</label>
                    <input class="input" type="text" id="meja" name="nilai" value="<?php echo e($meja); ?>" maxlength="30"<?php echo $nonaktif; ?>>
                    <button class="btn btn-plain btn-sm" type="submit"<?php echo $nonaktif; ?>>Ubah</button>
                </form>

                <div class="order-lines">
                    <?php if ($strukTerakhir): ?>
                    <p class="alert alert-ok">
                        Transaksi terakhir selesai. <a href="../transaksi/struk.php?id=<?php echo (int) $strukTerakhir; ?>" target="_blank">Lihat / cetak struk</a>
                    </p>
                    <?php endif; ?>

                    <?php if (!$baris): ?>
                    <p class="empty">Belum ada item. Klik menu di kiri untuk menambahkan.</p>
                    <?php endif; ?>

                    <?php foreach ($baris as $b): ?>
                    <div class="order-line">
                        <div class="order-line-info">
                            <span class="name"><?php echo e($b['nama']); ?></span>
                            <span class="meta"><?php echo e(rp($b['harga'])); ?> / item</span>
                        </div>
                        <div class="stepper">
                            <form method="post" action="proses_keranjang.php">
                                <?php echo bidang_kasir($kategori, $kata); ?>
                                <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                <button type="submit" name="aksi" value="kurang" aria-label="Kurangi <?php echo e($b['nama']); ?>"<?php echo $nonaktif; ?>>&minus;</button>
                            </form>
                            <span><?php echo (int) $b['qty']; ?></span>
                            <form method="post" action="proses_keranjang.php">
                                <?php echo bidang_kasir($kategori, $kata); ?>
                                <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                                <button type="submit" name="aksi" value="tambah" aria-label="Tambah <?php echo e($b['nama']); ?>"<?php echo $nonaktif; ?>>+</button>
                            </form>
                        </div>
                        <span class="order-line-sub"><?php echo e(rp($b['subtotal'])); ?></span>
                        <form method="post" action="proses_keranjang.php">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <input type="hidden" name="id" value="<?php echo $b['id']; ?>">
                            <button type="submit" name="aksi" value="hapus" class="order-line-remove" title="Hapus item"<?php echo $nonaktif; ?>>&times;</button>
                        </form>
                    </div>
                    <?php endforeach; ?>

                    <?php if ($openBill): ?>
                    <div class="bills">
                        <div class="bills-title">Open bill (<?php echo count($openBill); ?>)</div>
                        <?php foreach ($openBill as $bill): ?>
                        <div class="bill">
                            <div class="bill-info">
                                <span class="name"><?php echo e($bill['meja'] ?: $bill['kode']); ?></span>
                                <span class="meta"><?php echo e($bill['kode']); ?> &middot; <?php echo jam($bill['dibuat']); ?> &middot; <?php echo e(rp($bill['total'])); ?></span>
                            </div>
                            <a class="btn btn-outline btn-sm" href="../transaksi/struk.php?id=<?php echo (int) $bill['id']; ?>" target="_blank">Struk</a>
                            <form method="post" action="proses_bill.php">
                                <?php echo bidang_kasir($kategori, $kata); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $bill['id']; ?>">
                                <button class="btn btn-primary btn-sm" type="submit" name="aksi" value="buka"<?php echo $nonaktif; ?>>Lanjutkan</button>
                            </form>
                            <form method="post" action="proses_bill.php" class="form-hapus" data-pesan="Hapus open bill ini?">
                                <?php echo bidang_kasir($kategori, $kata); ?>
                                <input type="hidden" name="id" value="<?php echo (int) $bill['id']; ?>">
                                <button class="btn btn-danger btn-sm" type="submit" name="aksi" value="hapus"<?php echo $nonaktif; ?>>Hapus</button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="order-foot">
                    <div class="order-subtotal">
                        <span>Subtotal (<?php echo $jumlahItem; ?> item)</span>
                        <span><?php echo e(rp($total)); ?></span>
                    </div>
                    <div class="order-total">
                        <span class="order-total-label">TOTAL</span>
                        <span class="order-total-value"><?php echo e(rp($total)); ?></span>
                    </div>

                    <div class="pay-methods">
                        <?php foreach (['tunai' => 'Tunai', 'qris' => 'QRIS'] as $kunci => $label): ?>
                        <form method="post" action="proses_keranjang.php">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <input type="hidden" name="aksi" value="metode">
                            <input type="hidden" name="nilai" value="<?php echo $kunci; ?>">
                            <button class="seg-opt<?php echo $metode === $kunci ? ' is-active' : ''; ?>" type="submit"<?php echo $nonaktif; ?>><?php echo $label; ?></button>
                        </form>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($metode === 'qris'): ?>
                    <div class="qris-box">
                        <div class="qris-code" aria-hidden="true"></div>
                        <div class="qris-info">
                            <span class="label">Total yang dibayar via QRIS</span>
                            <span class="qris-total"><?php echo e(rp($total)); ?></span>
                            <span class="meta">Minta pelanggan membayar sesuai nominal ini, lalu cek notifikasi masuk di HP pemilik sebelum menekan Proses Bayar.</span>
                        </div>
                    </div>
                    <?php else: ?>
                    <form method="post" action="proses_keranjang.php" class="field">
                        <?php echo bidang_kasir($kategori, $kata); ?>
                        <input type="hidden" name="aksi" value="bayar">
                        <label class="label" for="bayar">Nominal bayar</label>
                        <span class="row-gap">
                            <input class="input input-strong" type="text" id="bayar" name="nilai" inputmode="numeric" style="flex:1"
                                   value="<?php echo $bayar ? number_format($bayar, 0, ',', '.') : ''; ?>" placeholder="0"<?php echo $nonaktif; ?>>
                            <button class="btn btn-outline" type="submit"<?php echo $nonaktif; ?>>OK</button>
                        </span>
                    </form>
                    <div class="quick-cash">
                        <form method="post" action="proses_keranjang.php">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <input type="hidden" name="aksi" value="pas">
                            <button type="submit"<?php echo $nonaktif; ?>>Uang pas</button>
                        </form>
                        <?php foreach ([20000, 50000, 100000] as $nominal): ?>
                        <form method="post" action="proses_keranjang.php">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <input type="hidden" name="aksi" value="bayar">
                            <input type="hidden" name="nilai" value="<?php echo $nominal; ?>">
                            <button type="submit"<?php echo $nonaktif; ?>><?php echo number_format($nominal, 0, ',', '.'); ?></button>
                        </form>
                        <?php endforeach; ?>
                    </div>
                    <div class="kembalian">
                        <span class="kembalian-label"><?php echo $kurang ? 'Kurang' : 'Kembalian'; ?></span>
                        <span class="kembalian-value"><?php echo e(rp($kurang ? $total - $bayar : ($bisaBayar ? $bayar - $total : 0))); ?></span>
                    </div>
                    <?php endif; ?>

                    <form method="post" action="proses_bayar.php">
                        <?php echo bidang_kasir($kategori, $kata); ?>
                        <button class="btn btn-primary pay-btn" type="submit"<?php echo $bisaBayar ? '' : ' disabled'; ?>>
                            <span>Proses Bayar</span><span>&rarr;</span>
                        </button>
                    </form>
                    <div class="row-gap">
                        <form method="post" action="proses_bill.php" style="flex:1">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <button class="btn btn-outline btn-md btn-full" type="submit" name="aksi" value="simpan"<?php echo ($bolehJual && $baris) ? '' : ' disabled'; ?>>Simpan open bill</button>
                        </form>
                        <form method="post" action="proses_keranjang.php">
                            <?php echo bidang_kasir($kategori, $kata); ?>
                            <button class="btn btn-plain btn-md" type="submit" name="aksi" value="kosongkan"<?php echo ($bolehJual && $baris) ? '' : ' disabled'; ?>>Kosongkan</button>
                        </form>
                    </div>
                </div>
            </aside>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
