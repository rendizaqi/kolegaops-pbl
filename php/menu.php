<?php
require __DIR__ . '/includes/functions.php';
$u = require_login();
$db = pdo();
$isOwner = $u['role'] === 'owner';

$kategoriList = ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
$q = trim($_GET['q'] ?? '');
$fcat = $_GET['kategori'] ?? '';
$fstat = $_GET['status'] ?? '';
$qs = http_build_query(array_filter(['q' => $q, 'kategori' => $fcat, 'status' => $fstat]));
$ret = 'menu.php' . ($qs ? '?' . $qs : '');
$toInt = fn($v) => (int) preg_replace('/\D/', '', (string) $v);

// ── Aksi ──
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    // Isi ulang stok menu: boleh untuk semua akun (pegawai melapor/menambah stok).
    if ($act === 'restock') {
        $jumlah = $toInt($_POST['jumlah'] ?? STOK_MENU_ISI_ULANG);
        if ($jumlah > 0) {
            $db->prepare('UPDATE menu SET stok = stok + ? WHERE id = ?')->execute([$jumlah, $id]);
            flash_set('ok', 'Stok menu bertambah ' . $jumlah . '.');
        }
        redirect($ret);
    }

    if (!$isOwner) {
        flash_set('err', 'Hanya pemilik yang bisa mengubah menu dan harga.');
        redirect($ret);
    }

    if ($act === 'save') {
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $kategori = in_array($_POST['kategori'] ?? '', $kategoriList, true) ? $_POST['kategori'] : 'Kopi';
        $hpp = $toInt($_POST['hpp'] ?? 0);
        $harga = $toInt($_POST['harga'] ?? 0);
        $stok = $toInt($_POST['stok'] ?? 0);
        $aktif = isset($_POST['aktif']) ? 1 : 0;
        if ($nama === '' || $harga <= 0) {
            flash_set('err', 'Nama dan harga jual wajib diisi.');
            redirect($ret . ($qs ? '&' : '?') . 'form=' . ($id ? 'edit&id=' . $id : 'new'));
        }
        if ($id > 0) {
            $db->prepare('UPDATE menu SET nama = ?, kategori = ?, hpp = ?, harga = ?, stok = ?, aktif = ? WHERE id = ?')
               ->execute([$nama, $kategori, $hpp, $harga, $stok, $aktif, $id]);
        } else {
            $db->prepare('INSERT INTO menu (nama, kategori, hpp, harga, stok, aktif) VALUES (?, ?, ?, ?, ?, ?)')
               ->execute([$nama, $kategori, $hpp, $harga, $stok, $aktif]);
        }
        flash_set('ok', 'Menu "' . $nama . '" disimpan.');
    } elseif ($act === 'toggle') {
        $db->prepare('UPDATE menu SET aktif = CASE WHEN aktif = 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
    } elseif ($act === 'delete') {
        $db->prepare('DELETE FROM menu WHERE id = ?')->execute([$id]);
        flash_set('ok', 'Menu dihapus. Riwayat transaksi lama tetap tersimpan.');
    }
    redirect($ret);
}

// ── Data ──
$all = $db->query('SELECT * FROM menu ORDER BY id')->fetchAll();
$rows = array_values(array_filter($all, function ($m) use ($q, $fcat, $fstat) {
    if ($q !== '' && stripos($m['nama'], $q) === false) return false;
    if ($fcat !== '' && $m['kategori'] !== $fcat) return false;
    if ($fstat === 'aktif' && !(int) $m['aktif']) return false;
    if ($fstat === 'nonaktif' && (int) $m['aktif']) return false;
    return true;
}));
$aktifCount = count(array_filter($all, fn($m) => (int) $m['aktif']));
$rendah = count(array_filter($all, fn($m) => (int) $m['aktif'] && (int) $m['stok'] <= STOK_MENU_RENDAH));

$form = $_GET['form'] ?? '';
$edit = null;
if ($isOwner && $form === 'edit') {
    $st = $db->prepare('SELECT * FROM menu WHERE id = ?');
    $st->execute([(int) ($_GET['id'] ?? 0)]);
    $edit = $st->fetch() ?: null;
}
$showForm = $isOwner && ($form === 'new' || $edit);

$cols = $isOwner
    ? 'grid-template-columns:44px 1.5fr .8fr .8fr .9fr .8fr .8fr .8fr 265px;gap:10px'
    : 'grid-template-columns:44px 2fr 1fr 1fr 1.2fr 260px';
$page = 'menu';
$title = 'Manajemen Menu';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Manajemen Menu</h1>
    <span class="meta"><?= count($all) ?> produk · <?= $aktifCount ?> aktif · <?= $rendah ?> stok menipis (≤ <?= STOK_MENU_RENDAH ?>)</span>
  </div>
  <?php if ($isOwner): ?>
    <a class="btn btn-primary" href="menu.php?form=new"><span>+</span><span>Tambah Produk</span></a>
  <?php endif; ?>
</div>

<form class="toolbar" method="get" action="menu.php">
  <input class="input" style="width:280px" type="text" name="q" value="<?= e($q) ?>" placeholder="Cari produk…">
  <select class="input" name="kategori">
    <option value="">Semua kategori</option>
    <?php foreach ($kategoriList as $c): ?><option value="<?= e($c) ?>" <?= $fcat === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
  </select>
  <select class="input" name="status">
    <option value="">Semua status</option>
    <option value="aktif" <?= $fstat === 'aktif' ? 'selected' : '' ?>>Aktif</option>
    <option value="nonaktif" <?= $fstat === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
  </select>
  <button class="btn btn-outline btn-md" type="submit">Terapkan</button>
</form>

<div class="panel table-wrap">
  <div class="trow thead" style="<?= $cols ?>">
    <span></span><span>Nama produk</span><span>Kategori</span>
    <?php if ($isOwner): ?><span>HPP</span><span>Harga jual</span><span>Profit</span><?php else: ?><span>Harga jual</span><?php endif; ?>
    <span>Stok</span><?php if ($isOwner): ?><span>Status</span><?php endif; ?><span>Aksi</span>
  </div>
  <?php foreach ($rows as $m):
    $stok = (int) $m['stok'];
    $profit = (int) $m['harga'] - (int) $m['hpp'];
    $margin = (int) $m['harga'] > 0 ? round($profit / (int) $m['harga'] * 100) : 0;
  ?>
    <div class="trow<?= (int) $m['aktif'] ? '' : ' row-off' ?>" style="<?= $cols ?>;padding-block:10px">
      <span class="photo thumb"></span>
      <span class="name"><?= e($m['nama']) ?></span>
      <span class="muted"><?= e($m['kategori']) ?></span>
      <?php if ($isOwner): ?>
        <span class="muted"><?= e(rp($m['hpp'])) ?></span>
        <span class="strong"><?= e(rp($m['harga'])) ?></span>
        <span class="profit"><?= e(rp($profit)) ?><small><?= $margin ?>%</small></span>
      <?php else: ?>
        <span class="strong"><?= e(rp($m['harga'])) ?></span>
      <?php endif; ?>
      <span>
        <?php if ($stok <= 0): ?><span class="badge badge-status badge-kritis">Habis</span>
        <?php elseif ($stok <= STOK_MENU_RENDAH): ?><span class="badge badge-status badge-menipis">● Sisa <?= $stok ?></span>
        <?php else: ?><span class="strong"><?= $stok ?></span><?php endif; ?>
      </span>
      <?php if ($isOwner): ?>
        <span>
          <form method="post" action="<?= e($ret) ?>"><?= csrf_field() ?><input type="hidden" name="act" value="toggle"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <button class="switch<?= (int) $m['aktif'] ? ' is-on' : '' ?>" type="submit">
              <span class="switch-track"><span class="switch-knob"></span></span><?= (int) $m['aktif'] ? 'Aktif' : 'Nonaktif' ?>
            </button></form>
        </span>
      <?php endif; ?>
      <span class="row-actions">
        <form method="post" action="<?= e($ret) ?>" class="restock"><?= csrf_field() ?>
          <input type="hidden" name="act" value="restock"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
          <input class="input input-sm" name="jumlah" value="<?= STOK_MENU_ISI_ULANG ?>" inputmode="numeric" aria-label="Jumlah stok ditambah">
          <button class="btn btn-outline btn-sm" type="submit">+ Stok</button>
        </form>
        <?php if ($isOwner): ?>
          <a class="btn btn-outline btn-sm" href="menu.php?form=edit&amp;id=<?= (int) $m['id'] ?>">Edit</a>
          <form method="post" action="<?= e($ret) ?>" onsubmit="return confirm('Hapus <?= e(addslashes($m['nama'])) ?>?')"><?= csrf_field() ?>
            <input type="hidden" name="act" value="delete"><input type="hidden" name="id" value="<?= (int) $m['id'] ?>">
            <button class="btn btn-danger btn-sm" type="submit">Hapus</button></form>
        <?php endif; ?>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><div class="empty">Tidak ada produk.</div><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <div class="dialog-backdrop">
    <form class="dialog" method="post" action="<?= e($ret) ?>"><?= csrf_field() ?>
      <input type="hidden" name="act" value="save">
      <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
      <div class="dialog-head">
        <h2 class="dialog-title"><?= $edit ? 'Edit Produk' : 'Tambah Produk' ?></h2>
        <span class="muted" style="font-size:13px">Isi HPP dan harga jual supaya profit terhitung otomatis.</span>
      </div>
      <div class="dialog-body">
        <label class="field"><span class="label">Nama produk</span>
          <input class="input" type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" autofocus></label>
        <label class="field"><span class="label">Kategori</span>
          <select class="input" name="kategori">
            <?php foreach ($kategoriList as $c): ?><option value="<?= e($c) ?>" <?= ($edit['kategori'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option><?php endforeach; ?>
          </select></label>
        <div class="row-gap">
          <label class="field" style="flex:1"><span class="label">HPP (Rp)</span>
            <input class="input" type="text" name="hpp" inputmode="numeric" value="<?= e($edit['hpp'] ?? '') ?>" placeholder="Modal per porsi"></label>
          <label class="field" style="flex:1"><span class="label">Harga jual (Rp)</span>
            <input class="input" type="text" name="harga" inputmode="numeric" required value="<?= e($edit['harga'] ?? '') ?>"></label>
        </div>
        <label class="field"><span class="label">Stok siap jual</span>
          <input class="input" type="text" name="stok" inputmode="numeric" value="<?= e($edit['stok'] ?? STOK_MENU_ISI_ULANG) ?>"></label>
        <label class="check"><input type="checkbox" name="aktif" value="1" <?= (!$edit || (int) $edit['aktif']) ? 'checked' : '' ?>> Aktif (tampil di kasir)</label>
      </div>
      <div class="dialog-actions">
        <a class="btn btn-plain btn-md" href="<?= e($ret) ?>">Batal</a>
        <button class="btn btn-primary btn-md" type="submit" style="padding-inline:18px">Simpan</button>
      </div>
    </form>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
