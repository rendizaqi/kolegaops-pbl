<?php
require __DIR__ . '/includes/functions.php';
$u = require_login();
$db = pdo();

$shift = current_shift();
$canSell = $shift && (int) $shift['kasir_id'] === (int) $u['id'];

$_SESSION['cart']   ??= [];   // [menu_id => qty]
$_SESSION['metode'] ??= 'tunai';
$_SESSION['bayar']  ??= 0;
$_SESSION['meja']   ??= 'Meja 1';

$menuAll = [];
foreach ($db->query('SELECT * FROM menu ORDER BY id')->fetchAll() as $m) {
    $menuAll[(int) $m['id']] = $m;
}

$cat = $_GET['cat'] ?? 'Semua';
$q = trim($_GET['q'] ?? '');
$qs = http_build_query(array_filter(['cat' => $cat === 'Semua' ? '' : $cat, 'q' => $q]));
$ret = 'kasir.php' . ($qs ? '?' . $qs : '');

$totalOf = function (array $cart) use ($menuAll): int {
    $t = 0;
    foreach ($cart as $id => $n) {
        if (isset($menuAll[$id])) {
            $t += (int) $menuAll[$id]['harga'] * $n;
        }
    }
    return $t;
};

// ── Aksi (semua lewat POST, lalu redirect) ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$canSell) {
        flash_set('err', 'Hanya kasir shift yang sedang berjalan yang bisa mencatat transaksi.');
        redirect($ret);
    }
    $act = $_POST['act'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);
    $cart = $_SESSION['cart'];

    switch ($act) {
        case 'add':
        case 'inc':
            $m = $menuAll[$id] ?? null;
            if (!$m || !(int) $m['aktif']) {
                flash_set('err', 'Menu tidak tersedia.');
            } elseif (($cart[$id] ?? 0) + 1 > (int) $m['stok']) {
                flash_set('err', 'Stok ' . $m['nama'] . ' tidak cukup (sisa ' . (int) $m['stok'] . ').');
            } else {
                $cart[$id] = ($cart[$id] ?? 0) + 1;
            }
            break;

        case 'dec':
            if (isset($cart[$id])) {
                $cart[$id]--;
                if ($cart[$id] <= 0) {
                    unset($cart[$id]);
                }
            }
            break;

        case 'remove':
            unset($cart[$id]);
            break;

        case 'clear':
            $cart = [];
            $_SESSION['bayar'] = 0;
            break;

        case 'metode':
            $_SESSION['metode'] = ($_POST['v'] ?? '') === 'qris' ? 'qris' : 'tunai';
            break;

        case 'bayar':
            $_SESSION['bayar'] = (int) preg_replace('/\D/', '', (string) ($_POST['v'] ?? ''));
            break;

        case 'pas':
            $_SESSION['bayar'] = $totalOf($cart);
            break;

        case 'meja':
            $_SESSION['meja'] = mb_substr(trim((string) ($_POST['v'] ?? '')), 0, 30) ?: 'Meja 1';
            break;

        case 'simpan_bill':
            if (!$cart) {
                flash_set('err', 'Pesanan masih kosong.');
                break;
            }
            $db->beginTransaction();
            $tid = save_order($db, $shift, $cart, $menuAll, 'open', '', 0, $_SESSION['meja']);
            $db->commit();
            $cart = [];
            $_SESSION['bayar'] = 0;
            flash_set('ok', 'Open bill ' . $_SESSION['meja'] . ' tersimpan (BILL-' . str_pad((string) $tid, 4, '0', STR_PAD_LEFT) . '). Bisa dilanjutkan kapan saja.');
            break;

        case 'buka_bill':
            if ($cart) {
                flash_set('err', 'Selesaikan atau kosongkan pesanan yang sedang dibuat dulu.');
                break;
            }
            $st = $db->prepare("SELECT * FROM transaksi WHERE id = ? AND status = 'open'");
            $st->execute([$id]);
            $bill = $st->fetch();
            if (!$bill) {
                flash_set('err', 'Open bill tidak ditemukan.');
                break;
            }
            $items = $db->prepare('SELECT menu_id, qty FROM transaksi_item WHERE transaksi_id = ?');
            $items->execute([$id]);
            foreach ($items->fetchAll() as $it) {
                if (isset($menuAll[(int) $it['menu_id']])) {
                    $cart[(int) $it['menu_id']] = (int) $it['qty'];
                }
            }
            $_SESSION['meja'] = $bill['meja'] ?: 'Meja 1';
            $db->prepare('DELETE FROM transaksi_item WHERE transaksi_id = ?')->execute([$id]);
            $db->prepare('DELETE FROM transaksi WHERE id = ?')->execute([$id]);
            break;

        case 'hapus_bill':
            $st = $db->prepare("SELECT id FROM transaksi WHERE id = ? AND status = 'open'");
            $st->execute([$id]);
            if ($st->fetchColumn()) {
                $db->prepare('DELETE FROM transaksi_item WHERE transaksi_id = ?')->execute([$id]);
                $db->prepare('DELETE FROM transaksi WHERE id = ?')->execute([$id]);
            }
            break;

        case 'proses':
            if (!$cart) {
                flash_set('err', 'Pesanan masih kosong.');
                break;
            }
            $total = $totalOf($cart);
            $metode = $_SESSION['metode'] === 'qris' ? 'qris' : 'tunai';
            $bayar = $metode === 'qris' ? $total : (int) $_SESSION['bayar'];
            if ($bayar < $total) {
                flash_set('err', 'Nominal bayar kurang dari total.');
                break;
            }
            try {
                $db->beginTransaction();
                $cek = $db->prepare('SELECT nama, stok FROM menu WHERE id = ?');
                foreach ($cart as $mid => $qty) {
                    $cek->execute([$mid]);
                    $row = $cek->fetch();
                    if (!$row || (int) $row['stok'] < $qty) {
                        throw new RuntimeException('Stok ' . ($row['nama'] ?? 'menu') . ' tidak cukup (sisa ' . (int) ($row['stok'] ?? 0) . ').');
                    }
                }
                $tid = save_order($db, $shift, $cart, $menuAll, 'lunas', $metode, $bayar, $_SESSION['meja']);
                $kurangi = $db->prepare('UPDATE menu SET stok = stok - ? WHERE id = ?');
                foreach ($cart as $mid => $qty) {
                    $kurangi->execute([$qty, $mid]);
                }
                $db->commit();
            } catch (Throwable $ex) {
                if ($db->inTransaction()) {
                    $db->rollBack();
                }
                flash_set('err', $ex->getMessage());
                break;
            }
            $_SESSION['last_struk'] = $tid;
            flash_set('ok', 'Pembayaran ' . rp($total) . ' (' . strtoupper($metode) . ') berhasil'
                . ($metode === 'tunai' ? ' · kembalian ' . rp($bayar - $total) : '') . '.');
            $cart = [];
            $_SESSION['bayar'] = 0;
            $_SESSION['metode'] = 'tunai';
            break;
    }
    $_SESSION['cart'] = $cart;
    redirect($ret);
}

// ── Data tampilan ──
$cart = $_SESSION['cart'];
$cards = array_values(array_filter($menuAll, function ($m) use ($cat, $q) {
    return (int) $m['aktif']
        && ($cat === 'Semua' || $m['kategori'] === $cat)
        && ($q === '' || stripos($m['nama'], $q) !== false);
}));

$lines = [];
$total = 0;
$itemCount = 0;
foreach ($cart as $id => $n) {
    if (!isset($menuAll[$id])) {
        continue;
    }
    $m = $menuAll[$id];
    $sub = (int) $m['harga'] * $n;
    $total += $sub;
    $itemCount += $n;
    $lines[] = ['id' => $id, 'nama' => $m['nama'], 'harga' => (int) $m['harga'], 'qty' => $n, 'sub' => $sub];
}

$metode = $_SESSION['metode'] === 'qris' ? 'qris' : 'tunai';
$bayar = $metode === 'qris' ? $total : (int) $_SESSION['bayar'];
$kurang = $metode === 'tunai' && $bayar > 0 && $bayar < $total;
$canPay = $canSell && $total > 0 && $bayar >= $total;
$meja = $_SESSION['meja'];
$dis = $canSell ? '' : ' disabled';

$bills = $db->query("SELECT * FROM transaksi WHERE status = 'open' ORDER BY id")->fetchAll();
$lastStruk = $_SESSION['last_struk'] ?? null;
$cats = ['Semua', 'Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
$nextNo = (int) $db->query('SELECT COALESCE(MAX(id),0) + 1 FROM transaksi')->fetchColumn();

$page = 'kasir';
$title = 'Kasir';
require __DIR__ . '/includes/header.php';
?>
<div class="kasir">
  <section class="kasir-menu">
    <div class="page-header">
      <div class="page-heading">
        <h1 class="page-title">Kasir</h1>
        <span class="meta">
          <?php if ($shift): ?>Shift <?= e($shift['nama']) ?> · Kasir: <b><?= e($shift['kasir']) ?></b>
          <?php else: ?>Belum ada shift aktif<?php endif; ?>
        </span>
      </div>
      <div class="kasir-header-right">
        <form method="get" action="kasir.php">
          <input type="hidden" name="cat" value="<?= e($cat) ?>">
          <input class="input kasir-search" type="text" name="q" value="<?= e($q) ?>" placeholder="Cari menu… (Enter)">
        </form>
        <span class="meta"><?= e(tgl_id()) ?></span>
      </div>
    </div>

    <?php if (!$shift): ?>
      <div class="gate">
        <h2>Belum ada shift yang berjalan</h2>
        <p>Buka shift dulu untuk mulai mencatat transaksi. Kasir shift adalah akun yang membuka shift.</p>
        <a class="btn btn-primary" href="shift.php"><span>Buka shift</span><span>→</span></a>
      </div>
    <?php else: ?>
      <?php if (!$canSell): ?>
        <div class="alert alert-warn">
          Shift ini dibuka oleh <b><?= e($shift['kasir']) ?></b>, jadi hanya akun itu yang bisa mencatat transaksi.
          Untuk ganti kasir: tutup shift di menu <a href="shift.php">Shift</a>, lalu login dengan akun kasir berikutnya.
        </div>
      <?php endif; ?>
      <div class="kasir-cats">
        <?php foreach ($cats as $c): ?>
          <a class="chip<?= $cat === $c ? ' is-active' : '' ?>"
             href="kasir.php?<?= e(http_build_query(array_filter(['cat' => $c === 'Semua' ? '' : $c, 'q' => $q]))) ?>"><?= e($c) ?></a>
        <?php endforeach; ?>
      </div>
      <div class="kasir-grid">
        <?php foreach ($cards as $m):
          $stok = (int) $m['stok'];
          $habis = $stok <= 0;
        ?>
          <form method="post" action="<?= e($ret) ?>" class="card-form">
            <?= csrf_field() ?>
            <input type="hidden" name="act" value="add">
            <input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <button class="menu-card" type="submit"<?= ($habis || !$canSell) ? ' disabled' : '' ?>>
              <div class="photo menu-card-photo">foto</div>
              <div class="menu-card-name"><?= e($m['nama']) ?></div>
              <div class="menu-card-foot">
                <span class="menu-card-price"><?= e(rp($m['harga'])) ?></span>
                <span class="menu-card-plus">+</span>
              </div>
              <?php if ($habis): ?>
                <span class="stok-tag stok-habis">Stok habis</span>
              <?php elseif ($stok <= STOK_MENU_RENDAH): ?>
                <span class="stok-tag stok-rendah">Sisa <?= $stok ?></span>
              <?php else: ?>
                <span class="stok-tag">Stok <?= $stok ?></span>
              <?php endif; ?>
            </button>
          </form>
        <?php endforeach; ?>
        <?php if (!$cards): ?><div class="empty">Tidak ada menu yang cocok.</div><?php endif; ?>
      </div>
    <?php endif; ?>
  </section>

  <aside class="order">
    <div class="order-head">
      <h2 class="order-title">Pesanan</h2>
      <span class="meta">#TRX-<?= str_pad((string) $nextNo, 4, '0', STR_PAD_LEFT) ?></span>
    </div>
    <form method="post" action="<?= e($ret) ?>" class="meja-form">
      <?= csrf_field() ?>
      <input type="hidden" name="act" value="meja">
      <label class="label" for="meja">Meja / nama</label>
      <input class="input" id="meja" name="v" value="<?= e($meja) ?>" maxlength="30"<?= $dis ?>>
      <button class="btn btn-plain btn-sm" type="submit"<?= $dis ?>>Ubah</button>
    </form>

    <div class="order-lines">
      <?php if ($lastStruk): ?>
        <div class="alert alert-ok" style="margin:12px 20px 0">
          Transaksi terakhir selesai. <a href="struk.php?id=<?= (int) $lastStruk ?>" target="_blank">Lihat / cetak struk</a>
        </div>
      <?php endif; ?>
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
            <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>">
              <button name="act" value="dec" aria-label="Kurangi <?= e($l['nama']) ?>"<?= $dis ?>>−</button></form>
            <span><?= (int) $l['qty'] ?></span>
            <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>">
              <button name="act" value="inc" aria-label="Tambah <?= e($l['nama']) ?>"<?= $dis ?>>+</button></form>
          </div>
          <span class="order-line-sub"><?= e(rp($l['sub'])) ?></span>
          <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= $l['id'] ?>">
            <button class="order-line-remove" name="act" value="remove" title="Hapus item"<?= $dis ?>>×</button></form>
        </div>
      <?php endforeach; ?>

      <?php if ($bills): ?>
        <div class="bills">
          <div class="bills-title">Open bill (<?= count($bills) ?>)</div>
          <?php foreach ($bills as $b): ?>
            <div class="bill">
              <div class="bill-info">
                <span class="name"><?= e($b['meja'] ?: $b['kode']) ?></span>
                <span class="meta"><?= e($b['kode']) ?> · <?= e($b['waktu']) ?> · <?= e(rp($b['total'])) ?></span>
              </div>
              <a class="btn btn-outline btn-sm" href="struk.php?id=<?= (int) $b['id'] ?>" target="_blank">Struk</a>
              <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                <button class="btn btn-primary btn-sm" name="act" value="buka_bill"<?= $dis ?>>Lanjutkan</button></form>
              <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="id" value="<?= (int) $b['id'] ?>">
                <button class="btn btn-danger btn-sm" name="act" value="hapus_bill"<?= $dis ?>>Hapus</button></form>
            </div>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
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

      <div class="pay-methods">
        <?php foreach (['tunai' => 'Tunai', 'qris' => 'QRIS'] as $k => $label): ?>
          <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?>
            <input type="hidden" name="act" value="metode"><input type="hidden" name="v" value="<?= $k ?>">
            <button class="seg-opt<?= $metode === $k ? ' is-active' : '' ?>" type="submit"<?= $dis ?>><?= $label ?></button>
          </form>
        <?php endforeach; ?>
      </div>

      <?php if ($metode === 'qris'): ?>
        <div class="qris-box">
          <div class="qris-code" aria-hidden="true"></div>
          <div class="qris-info">
            <span class="label">Total yang dibayar via QRIS</span>
            <span class="qris-total"><?= e(rp($total)) ?></span>
            <span class="meta">Minta pelanggan membayar sesuai nominal ini, lalu cek notifikasi masuk di HP pemilik sebelum menekan Proses Bayar.</span>
          </div>
        </div>
      <?php else: ?>
        <form method="post" action="<?= e($ret) ?>" class="field"><?= csrf_field() ?>
          <input type="hidden" name="act" value="bayar">
          <label class="label" for="bayar">Nominal bayar</label>
          <div class="row-gap">
            <input class="input input-strong" id="bayar" name="v" inputmode="numeric" style="flex:1"
                   value="<?= $bayar ? e(number_format($bayar, 0, ',', '.')) : '' ?>" placeholder="0"<?= $dis ?>>
            <button class="btn btn-outline" type="submit" style="height:42px"<?= $dis ?>>OK</button>
          </div>
        </form>
        <div class="quick-cash">
          <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="act" value="pas">
            <button type="submit"<?= $dis ?>>Uang pas</button></form>
          <?php foreach ([20000, 50000, 100000] as $nom): ?>
            <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?>
              <input type="hidden" name="act" value="bayar"><input type="hidden" name="v" value="<?= $nom ?>">
              <button type="submit"<?= $dis ?>><?= number_format($nom, 0, ',', '.') ?></button></form>
          <?php endforeach; ?>
        </div>
        <div class="kembalian">
          <span class="kembalian-label"><?= $kurang ? 'Kurang' : 'Kembalian' ?></span>
          <span class="kembalian-value"><?= e(rp($kurang ? $total - $bayar : ($canPay ? $bayar - $total : 0))) ?></span>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?>
        <button class="btn btn-primary pay-btn" type="submit" name="act" value="proses"<?= $canPay ? '' : ' disabled' ?>>
          <span>Proses Bayar</span><span>→</span>
        </button>
      </form>
      <div class="row-gap">
        <form method="post" action="<?= e($ret) ?>" style="flex:1"><?= csrf_field() ?>
          <button class="btn btn-outline btn-md btn-full" name="act" value="simpan_bill"<?= ($canSell && $lines) ? '' : ' disabled' ?>>Simpan open bill</button></form>
        <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?>
          <button class="btn btn-plain btn-md" name="act" value="clear"<?= ($canSell && $lines) ? '' : ' disabled' ?>>Kosongkan</button></form>
      </div>
    </div>
  </aside>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
