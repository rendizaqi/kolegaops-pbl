<?php
require __DIR__ . '/includes/functions.php';
$db = pdo();

// ── Aksi ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_update'])) {
    $id     = (int) ($_POST['id'] ?? 0);
    $mode   = $_POST['mode'] ?? 'Tambah';
    $jumlah = (float) str_replace(',', '.', (string) ($_POST['jumlah'] ?? '0'));
    $st = $db->prepare('SELECT qty FROM bahan WHERE id=?');
    $st->execute([$id]);
    $cur = $st->fetchColumn();
    if ($cur !== false) {
        $new = $mode === 'Tambah' ? $cur + $jumlah : ($mode === 'Kurangi' ? $cur - $jumlah : $jumlah);
        $new = max(0, round($new, 2));
        $db->prepare('UPDATE bahan SET qty=? WHERE id=?')->execute([$new, $id]);
    }
    redirect('stok.php');
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_new'])) {
    $nama    = trim($_POST['nama'] ?? '');
    $qty     = (float) str_replace(',', '.', (string) ($_POST['qty'] ?? '0'));
    $satuan  = trim($_POST['satuan'] ?? '');
    $minimum = (float) str_replace(',', '.', (string) ($_POST['minimum'] ?? '0'));
    if ($nama !== '') {
        $db->prepare('INSERT INTO bahan (nama, qty, satuan, minimum) VALUES (?, ?, ?, ?)')
           ->execute([$nama, $qty, $satuan, $minimum]);
    }
    redirect('stok.php');
}

// ── Data ──
$rows = $db->query('SELECT * FROM bahan ORDER BY id')->fetchAll();
$count = ['kritis' => 0, 'menipis' => 0, 'aman' => 0];
foreach ($rows as $r) {
    $count[stok_status((float) $r['qty'], (float) $r['minimum'])]++;
}

$edit = null;
if (isset($_GET['edit'])) {
    $st = $db->prepare('SELECT * FROM bahan WHERE id=?');
    $st->execute([(int) $_GET['edit']]);
    $edit = $st->fetch() ?: null;
}
$showNew = ($_GET['form'] ?? '') === 'new';

$barColor = ['kritis' => 'var(--danger)', 'menipis' => 'var(--warn-line)', 'aman' => 'var(--blue)'];
$modes = ['Tambah', 'Kurangi', 'Set jumlah'];
$cols = 'grid-template-columns:2fr 1.6fr 0.8fr 1fr 1fr 110px';
$page = 'stok';
$title = 'Manajemen Stok';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Manajemen Stok</h1>
    <span class="meta">Bahan baku · terakhir diperbarui <?= e(date('H:i')) ?></span>
  </div>
  <a class="btn btn-primary" href="stok.php?form=new"><span>+</span><span>Tambah Bahan</span></a>
</div>

<div class="stok-summary">
  <div><span class="stat-label">Total bahan</span><span class="stok-summary-value"><?= count($rows) ?></span></div>
  <div class="is-warn"><span class="stat-label">Menipis</span><span class="stok-summary-value"><?= $count['menipis'] ?></span></div>
  <div class="is-danger"><span class="stat-label">Kritis</span><span class="stok-summary-value"><?= $count['kritis'] ?></span></div>
</div>

<div class="panel table-wrap" style="margin-top:16px">
  <div class="trow thead" style="<?= $cols ?>">
    <span>Nama bahan</span><span>Stok saat ini</span><span>Satuan</span><span>Batas min.</span><span>Status</span><span>Aksi</span>
  </div>
  <?php foreach ($rows as $s):
    $st = stok_status((float) $s['qty'], (float) $s['minimum']);
    $pct = min(100, $s['qty'] / max(0.01, $s['minimum'] * 3) * 100);
  ?>
    <div class="trow" style="<?= $cols ?>">
      <span class="name"><?= e($s['nama']) ?></span>
      <span class="stok-level">
        <span class="strong stok-qty"><?= e(num_id($s['qty'])) ?></span>
        <span class="stok-bar"><span style="width:<?= (int) round($pct) ?>%;background:<?= $barColor[$st] ?>"></span></span>
      </span>
      <span class="muted"><?= e($s['satuan']) ?></span>
      <span class="muted"><?= e(num_id($s['minimum'])) ?></span>
      <span>
        <?php if ($st === 'kritis'): ?><span class="badge badge-status badge-kritis">● Kritis</span>
        <?php elseif ($st === 'menipis'): ?><span class="badge badge-status badge-menipis">● Menipis</span>
        <?php else: ?><span class="badge badge-status badge-aman">Aman</span><?php endif; ?>
      </span>
      <a class="btn btn-outline btn-sm" href="stok.php?edit=<?= $s['id'] ?>">Update</a>
    </div>
  <?php endforeach; ?>
</div>

<?php if ($edit): ?>
  <div class="dialog-backdrop">
    <form class="dialog" method="post" action="stok.php">
      <div class="dialog-head">
        <h2 class="dialog-title">Update Stok</h2>
        <span class="muted" style="font-size:13px"><?= e($edit['nama']) ?> · stok saat ini <?= e(num_id($edit['qty'])) ?> <?= e($edit['satuan']) ?></span>
      </div>
      <div class="dialog-body">
        <input type="hidden" name="id" value="<?= (int) $edit['id'] ?>">
        <label class="field">
          <span class="label">Mode</span>
          <select class="input" name="mode">
            <?php foreach ($modes as $m): ?><option value="<?= e($m) ?>"><?= e($m) ?></option><?php endforeach; ?>
          </select>
        </label>
        <label class="field">
          <span class="label">Jumlah (<?= e($edit['satuan']) ?>)</span>
          <input class="input input-strong" type="text" name="jumlah" inputmode="decimal" autofocus>
        </label>
        <textarea class="input" name="catatan" placeholder="Catatan (opsional) — mis. belanja pasar pagi"></textarea>
      </div>
      <div class="dialog-actions">
        <a class="btn btn-plain btn-md" href="stok.php">Batal</a>
        <button class="btn btn-primary btn-md" type="submit" name="save_update" value="1" style="padding-inline:18px">Simpan</button>
      </div>
    </form>
  </div>
<?php endif; ?>

<?php if ($showNew): ?>
  <div class="dialog-backdrop">
    <form class="dialog" method="post" action="stok.php">
      <div class="dialog-head">
        <h2 class="dialog-title">Tambah Bahan</h2>
        <span class="muted" style="font-size:13px">Tambah bahan baku baru ke daftar stok.</span>
      </div>
      <div class="dialog-body">
        <label class="field"><span class="label">Nama bahan</span><input class="input" type="text" name="nama" required autofocus></label>
        <label class="field"><span class="label">Stok saat ini</span><input class="input" type="text" name="qty" inputmode="decimal" value="0"></label>
        <label class="field"><span class="label">Satuan</span><input class="input" type="text" name="satuan" placeholder="kg / liter / pcs"></label>
        <label class="field"><span class="label">Batas minimum</span><input class="input" type="text" name="minimum" inputmode="decimal" value="0"></label>
      </div>
      <div class="dialog-actions">
        <a class="btn btn-plain btn-md" href="stok.php">Batal</a>
        <button class="btn btn-primary btn-md" type="submit" name="save_new" value="1" style="padding-inline:18px">Simpan</button>
      </div>
    </form>
  </div>
<?php endif; ?>
<?php require __DIR__ . '/includes/footer.php'; ?>
