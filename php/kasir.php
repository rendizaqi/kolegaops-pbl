<?php
require __DIR__ . '/includes/functions.php';
$db = pdo();

// Keranjang & nominal bayar disimpan di sesi (per kasir/browser).
if (!isset($_SESSION['order'])) {
    $_SESSION['order'] = [2 => 2, 8 => 1]; // isi contoh saat pertama buka
}
if (!isset($_SESSION['bayar'])) {
    $_SESSION['bayar'] = 0;
}

// Peta harga & nama menu (untuk hitung total & baris pesanan).
$menuAll = [];
foreach ($db->query('SELECT * FROM menu ORDER BY id')->fetchAll() as $m) {
    $menuAll[(int) $m['id']] = $m;
}

$cat = $_GET['cat'] ?? 'Semua';
$q   = trim($_GET['q'] ?? '');
$ret = 'kasir.php?' . http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q]));

// ── Aksi (Post/Redirect/Get supaya refresh tidak menggandakan) ──
$order = &$_SESSION['order'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['proses'])) {
        $total = 0;
        foreach ($order as $id => $qty) {
            $total += ($menuAll[$id]['harga'] ?? 0) * $qty;
        }
        $bayar = (int) $_SESSION['bayar'];
        if ($total > 0 && $bayar >= $total) {
            $count = (int) $db->query('SELECT COUNT(*) FROM transaksi')->fetchColumn();
            $kode  = 'TRX-' . str_pad((string) ($count + 1), 4, '0', STR_PAD_LEFT);
            $st = $db->prepare('INSERT INTO transaksi (kode, waktu, tanggal, kasir, shift, total, status)
                                VALUES (?, ?, ?, ?, ?, ?, ?)');
            $st->execute([$kode, date('H:i'), date('Y-m-d'), 'Rina', 'Pagi', $total, 'lunas']);
            $_SESSION['flash']['paid'] = 'Pembayaran ' . rp($total) . ' berhasil · kembalian ' . rp($bayar - $total);
            $order = [];
            $_SESSION['bayar'] = 0;
        }
        redirect($ret);
    }
    if (isset($_POST['bayar'])) {
        $_SESSION['bayar'] = (int) preg_replace('/\D/', '', (string) $_POST['bayar']);
        redirect($ret);
    }
}

if (isset($_GET['add']))    { $id = (int) $_GET['add'];  $order[$id] = ($order[$id] ?? 0) + 1; $_SESSION['bayar'] = 0; redirect($ret); }
if (isset($_GET['inc']))    { $id = (int) $_GET['inc'];  $order[$id] = ($order[$id] ?? 0) + 1; redirect($ret); }
if (isset($_GET['dec']))    { $id = (int) $_GET['dec'];  $order[$id] = ($order[$id] ?? 0) - 1; if ($order[$id] <= 0) unset($order[$id]); redirect($ret); }
if (isset($_GET['remove'])) { unset($order[(int) $_GET['remove']]); redirect($ret); }
if (isset($_GET['bayar']))  { $_SESSION['bayar'] = (int) $_GET['bayar']; redirect($ret); }

// ── Data untuk tampilan ──
$cards = array_values(array_filter($menuAll, function ($m) use ($cat, $q) {
    if (!$m['aktif']) return false;
    if ($cat !== 'Semua' && $m['kategori'] !== $cat) return false;
    if ($q !== '' && stripos($m['nama'], $q) === false) return false;
    return true;
}));

$lines = [];
$total = 0;
$itemCount = 0;
foreach ($order as $id => $qty) {
    if (!isset($menuAll[$id])) continue;
    $m = $menuAll[$id];
    $sub = $m['harga'] * $qty;
    $total += $sub;
    $itemCount += $qty;
    $lines[] = ['id' => $id, 'nama' => $m['nama'], 'harga' => $m['harga'], 'qty' => $qty, 'sub' => $sub];
}

$bayar = (int) $_SESSION['bayar'];
$kurang = $bayar > 0 && $bayar < $total;
$canPay = $total > 0 && $bayar >= $total;
$paidMsg = take_flash('paid');
$kode = 'TRX-' . str_pad((string) ((int) $db->query('SELECT COUNT(*) FROM transaksi')->fetchColumn() + 1), 4, '0', STR_PAD_LEFT);

$cats = ['Semua', 'Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
$quick = [['Uang pas', $total], ['20.000', 20000], ['50.000', 50000], ['100.000', 100000]];

$page = 'kasir';
$title = 'Kasir';
require __DIR__ . '/includes/header.php';
?>
<div class="kasir">
  <section class="kasir-menu">
    <div class="page-header">
      <h1 class="page-title">Kasir</h1>
      <div class="kasir-header-right">
        <form method="get" action="kasir.php">
          <input type="hidden" name="cat" value="<?= e($cat) ?>">
          <input class="input kasir-search" type="text" name="q" value="<?= e($q) ?>" placeholder="Cari menu…">
        </form>
        <span class="meta"><?= e(date('l, d M Y')) ?></span>
      </div>
    </div>
    <div class="kasir-cats">
      <?php foreach ($cats as $c): ?>
        <a class="chip<?= $cat === $c ? ' is-active' : '' ?>"
           href="kasir.php?<?= http_build_query(array_filter(['cat' => $c === 'Semua' ? '' : $c, 'q' => $q])) ?>"><?= e($c) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="kasir-grid">
      <?php foreach ($cards as $m): ?>
        <a class="menu-card" href="kasir.php?add=<?= (int) $m['id'] ?>&amp;<?= http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q])) ?>">
          <div class="photo menu-card-photo">foto</div>
          <div class="menu-card-name"><?= e($m['nama']) ?></div>
          <div class="menu-card-foot">
            <span class="menu-card-price"><?= e(rp($m['harga'])) ?></span>
            <span class="menu-card-plus">+</span>
          </div>
        </a>
      <?php endforeach; ?>
      <?php if (!$cards): ?><div class="empty">Tidak ada menu yang cocok.</div><?php endif; ?>
    </div>
  </section>

  <aside class="order">
    <div class="order-head">
      <h2 class="order-title">Pesanan</h2>
      <span class="meta">#<?= e($kode) ?> · Meja 4</span>
    </div>
    <div class="order-lines">
      <?php if (!$lines): ?>
        <div class="empty">Belum ada item. Klik menu di kiri untuk menambahkan.</div>
      <?php endif; ?>
      <?php foreach ($lines as $l): ?>
        <div class="order-line">
          <div class="order-line-info">
            <span class="name"><?= e($l['nama']) ?></span>
            <span class="meta"><?= e(rp($l['harga'])) ?> / item</span>
          </div>
          <div class="stepper">
            <a href="kasir.php?dec=<?= $l['id'] ?>&amp;<?= http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q])) ?>" aria-label="Kurangi">−</a>
            <span><?= (int) $l['qty'] ?></span>
            <a href="kasir.php?inc=<?= $l['id'] ?>&amp;<?= http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q])) ?>" aria-label="Tambah">+</a>
          </div>
          <span class="order-line-sub"><?= e(rp($l['sub'])) ?></span>
          <a class="order-line-remove" href="kasir.php?remove=<?= $l['id'] ?>&amp;<?= http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q])) ?>" title="Hapus item">×</a>
        </div>
      <?php endforeach; ?>
    </div>
    <div class="order-foot">
      <div class="order-subtotal">
        <span>Subtotal (<?= (int) $itemCount ?> item)</span>
        <span><?= e(rp($total)) ?></span>
      </div>
      <div class="order-total">
        <span class="order-total-label">TOTAL</span>
        <span class="order-total-value"><?= e(rp($total)) ?></span>
      </div>
      <form method="post" action="<?= e($ret) ?>" class="field">
        <label class="label" for="bayar">Nominal bayar</label>
        <div style="display:flex;gap:6px">
          <input class="input input-strong" id="bayar" name="bayar" inputmode="numeric" style="flex:1"
                 value="<?= $bayar ? e(number_format($bayar, 0, ',', '.')) : '' ?>" placeholder="0">
          <button class="btn btn-outline" type="submit" style="height:42px">OK</button>
        </div>
      </form>
      <div class="quick-cash">
        <?php foreach ($quick as $qc): ?>
          <a href="kasir.php?bayar=<?= (int) $qc[1] ?>&amp;<?= http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q])) ?>"><?= e($qc[0]) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="kembalian">
        <span class="kembalian-label"><?= $kurang ? 'Kurang' : 'Kembalian' ?></span>
        <span class="kembalian-value"><?= e(rp($kurang ? $total - $bayar : ($canPay ? $bayar - $total : 0))) ?></span>
      </div>
      <form method="post" action="<?= e($ret) ?>">
        <button class="btn btn-primary pay-btn" type="submit" name="proses" value="1" <?= $canPay ? '' : 'disabled' ?>>
          <span>Proses Bayar</span><span>→</span>
        </button>
      </form>
      <?php if ($paidMsg): ?><div class="paid-msg">✓ <?= e($paidMsg) ?></div><?php endif; ?>
    </div>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
