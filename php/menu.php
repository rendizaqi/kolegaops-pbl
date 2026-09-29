<?php
require __DIR__ . '/includes/functions.php';
$db = pdo();

$kategoriList = ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];

$q      = trim($_GET['q'] ?? '');
$fcat   = $_GET['kategori'] ?? '';
$fstat  = $_GET['status'] ?? '';
$ret = 'menu.php?' . http_build_query(array_filter(['q' => $q, 'kategori' => $fcat, 'status' => $fstat]));

// ── Aksi ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save'])) {
    $nama     = trim($_POST['nama'] ?? '');
    $kategori = $_POST['kategori'] ?? 'Kopi';
    $harga    = (int) preg_replace('/\D/', '', (string) ($_POST['harga'] ?? '0'));
    $aktif    = isset($_POST['aktif']) ? 1 : 0;
    $id       = (int) ($_POST['id'] ?? 0);
    if ($nama !== '') {
        if ($id > 0) {
            $st = $db->prepare('UPDATE menu SET nama=?, kategori=?, harga=?, aktif=? WHERE id=?');
            $st->execute([$nama, $kategori, $harga, $aktif, $id]);
        } else {
            $st = $db->prepare('INSERT INTO menu (nama, kategori, harga, aktif) VALUES (?, ?, ?, ?)');
            $st->execute([$nama, $kategori, $harga, $aktif]);
        }
    }
    redirect($ret);
}
if (isset($_GET['toggle'])) {
    $st = $db->prepare('UPDATE menu SET aktif = CASE WHEN aktif=1 THEN 0 ELSE 1 END WHERE id=?');
    $st->execute([(int) $_GET['toggle']]);
    redirect($ret);
}
if (isset($_GET['delete'])) {
    $st = $db->prepare('DELETE FROM menu WHERE id=?');
    $st->execute([(int) $_GET['delete']]);
    redirect($ret);
}

// ── Data ──
$all = $db->query('SELECT * FROM menu ORDER BY id')->fetchAll();
$rows = array_values(array_filter($all, function ($m) use ($q, $fcat, $fstat) {
    if ($q !== '' && stripos($m['nama'], $q) === false) return false;
    if ($fcat !== '' && $m['kategori'] !== $fcat) return false;
    if ($fstat === 'aktif' && !$m['aktif']) return false;
    if ($fstat === 'nonaktif' && $m['aktif']) return false;
    return true;
}));
$aktifCount = count(array_filter($all, fn($m) => $m['aktif']));

// Dialog tambah/edit.
$form = $_GET['form'] ?? '';
$edit = null;
if ($form === 'edit' && isset($_GET['id'])) {
    $st = $db->prepare('SELECT * FROM menu WHERE id=?');
    $st->execute([(int) $_GET['id']]);
    $edit = $st->fetch() ?: null;
}
$showForm = $form === 'new' || $edit;

$cols = 'grid-template-columns:56px 2fr 1fr 1fr 1fr 160px';
$page = 'menu';
$title = 'Manajemen Menu';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Manajemen Menu</h1>
    <span class="meta"><?= count($all) ?> produk · <?= $aktifCount ?> aktif</span>
  </div>
  <a class="btn btn-primary" href="menu.php?form=new"><span>+</span><span>Tambah Produk</span></a>
</div>

<form class="toolbar" method="get" action="menu.php">
  <input class="input" style="width:300px" type="text" name="q" value="<?= e($q) ?>" placeholder="Cari produk…">
  <select class="input" name="kategori">
    <option value="">Semua kategori</option>
    <?php foreach ($kategoriList as $c): ?>
      <option value="<?= e($c) ?>" <?= $fcat === $c ? 'selected' : '' ?>><?= e($c) ?></option>
    <?php endforeach; ?>
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
    <span></span><span>Nama produk</span><span>Kategori</span><span>Harga</span><span>Status</span><span>Aksi</span>
  </div>
  <?php foreach ($rows as $m): ?>
    <div class="trow" style="<?= $cols ?>;padding-block:10px">
      <span class="photo thumb"></span>
      <span class="name"><?= e($m['nama']) ?></span>
      <span class="muted"><?= e($m['kategori']) ?></span>
      <span class="strong"><?= e(rp($m['harga'])) ?></span>
      <span>
        <a class="switch<?= $m['aktif'] ? ' is-on' : '' ?>" href="menu.php?toggle=<?= $m['id'] ?>&amp;<?= http_build_query(array_filter(['q' => $q, 'kategori' => $fcat, 'status' => $fstat])) ?>">
          <span class="switch-track"><span class="switch-knob"></span></span><?= $m['aktif'] ? 'Aktif' : 'Nonaktif' ?>
        </a>
      </span>
      <span class="row-actions">
        <a class="btn btn-outline btn-sm" href="menu.php?form=edit&amp;id=<?= $m['id'] ?>">Edit</a>
        <a class="btn btn-danger btn-sm" href="menu.php?delete=<?= $m['id'] ?>&amp;<?= http_build_query(array_filter(['q' => $q, 'kategori' => $fcat, 'status' => $fstat])) ?>">Hapus</a>
      </span>
    </div>
  <?php endforeach; ?>
  <?php if (!$rows): ?><div class="empty">Tidak ada produk.</div><?php endif; ?>
</div>

<?php if ($showForm): ?>
  <div class="dialog-backdrop">
    <form class="dialog" method="post" action="<?= e($ret) ?>">
      <div class="dialog-head">
        <h2 class="dialog-title"><?= $edit ? 'Edit Produk' : 'Tambah Produk' ?></h2>
        <span class="muted" style="font-size:13px">Lengkapi data produk di bawah.</span>
      </div>
      <div class="dialog-body">
        <?php if ($edit): ?><input type="hidden" name="id" value="<?= (int) $edit['id'] ?>"><?php endif; ?>
        <label class="field">
          <span class="label">Nama produk</span>
          <input class="input" type="text" name="nama" required value="<?= e($edit['nama'] ?? '') ?>" autofocus>
        </label>
        <label class="field">
          <span class="label">Kategori</span>
          <select class="input" name="kategori">
            <?php foreach ($kategoriList as $c): ?>
              <option value="<?= e($c) ?>" <?= ($edit['kategori'] ?? '') === $c ? 'selected' : '' ?>><?= e($c) ?></option>
            <?php endforeach; ?>
          </select>
        </label>
        <label class="field">
          <span class="label">Harga (Rp)</span>
          <input class="input input-strong" type="text" name="harga" inputmode="numeric" value="<?= e($edit['harga'] ?? '') ?>">
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:14px">
          <input type="checkbox" name="aktif" value="1" <?= (!$edit || $edit['aktif']) ? 'checked' : '' ?>> Aktif (tampil di kasir)
        </label>
      </div>
      <div class="dialog-actions">
        <a class="btn btn-plain btn-md" href="<?= e($ret) ?>">Batal</a>
        <button class="btn btn-primary btn-md" type="submit" name="save" value="1" style="padding-inline:18px">Simpan</button>
      </div>
    </form>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
