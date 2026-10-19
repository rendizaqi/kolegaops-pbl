<?php
require __DIR__ . '/includes/functions.php';
$u = require_owner();
$db = pdo();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $act = $_POST['act'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    if ($act === 'baru') {
        $nama = trim((string) ($_POST['nama'] ?? ''));
        $user = strtolower(trim((string) ($_POST['username'] ?? '')));
        $pass = (string) ($_POST['password'] ?? '');
        $role = ($_POST['role'] ?? '') === 'owner' ? 'owner' : 'pegawai';
        $ada = $db->prepare('SELECT COUNT(*) FROM users WHERE username = ?');
        $ada->execute([$user]);
        if ($nama === '' || !preg_match('/^[a-z0-9_.]{3,30}$/', $user)) {
            flash_set('err', 'Isi nama, dan username 3–30 karakter (huruf kecil, angka, titik, garis bawah).');
        } elseif (strlen($pass) < 6) {
            flash_set('err', 'Kata sandi minimal 6 karakter.');
        } elseif ((int) $ada->fetchColumn() > 0) {
            flash_set('err', 'Username sudah dipakai.');
        } else {
            $db->prepare('INSERT INTO users (nama, username, password_hash, role, aktif) VALUES (?, ?, ?, ?, 1)')
               ->execute([$nama, $user, password_hash($pass, PASSWORD_DEFAULT), $role]);
            flash_set('ok', 'Akun ' . $nama . ' dibuat.');
        }
    } elseif ($act === 'toggle') {
        if ($id === (int) $u['id']) {
            flash_set('err', 'Akun Anda sendiri tidak bisa dinonaktifkan.');
        } else {
            $db->prepare('UPDATE users SET aktif = CASE WHEN aktif = 1 THEN 0 ELSE 1 END WHERE id = ?')->execute([$id]);
        }
    } elseif ($act === 'reset') {
        $pass = (string) ($_POST['password'] ?? '');
        if (strlen($pass) < 6) {
            flash_set('err', 'Kata sandi baru minimal 6 karakter.');
        } else {
            $db->prepare('UPDATE users SET password_hash = ? WHERE id = ?')->execute([password_hash($pass, PASSWORD_DEFAULT), $id]);
            flash_set('ok', 'Kata sandi diganti.');
        }
    }
    redirect('users.php');
}

$rows = $db->query('SELECT * FROM users ORDER BY id')->fetchAll();
$cols = 'grid-template-columns:1.2fr 1fr .8fr .8fr 2fr';
$page = 'users';
$title = 'Akun';
require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div class="page-heading">
    <h1 class="page-title">Akun</h1>
    <span class="meta">Setiap orang punya akun sendiri · 1 pemilik, sisanya pegawai</span>
  </div>
</div>

<div class="content">
  <section class="panel table-block">
    <div class="trow thead" style="<?= $cols ?>"><span>Nama</span><span>Username</span><span>Peran</span><span>Status</span><span>Aksi</span></div>
    <?php foreach ($rows as $r): ?>
      <div class="trow" style="<?= $cols ?>">
        <span class="name"><?= e($r['nama']) ?></span><span class="mono"><?= e($r['username']) ?></span>
        <span><?= $r['role'] === 'owner' ? '<span class="badge badge-ok">Pemilik</span>' : 'Pegawai' ?></span>
        <span><?= (int) $r['aktif'] ? '<span class="badge badge-ok">Aktif</span>' : '<span class="badge badge-bad">Nonaktif</span>' ?></span>
        <span class="row-actions">
          <form method="post" class="restock"><?= csrf_field() ?><input type="hidden" name="act" value="reset"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
            <input class="input input-sm" style="width:130px" type="password" name="password" placeholder="Sandi baru" minlength="6" aria-label="Kata sandi baru <?= e($r['nama']) ?>">
            <button class="btn btn-outline btn-sm" type="submit">Ganti sandi</button></form>
          <?php if ((int) $r['id'] !== (int) $u['id']): ?>
            <form method="post"><?= csrf_field() ?><input type="hidden" name="act" value="toggle"><input type="hidden" name="id" value="<?= (int) $r['id'] ?>">
              <button class="btn btn-danger btn-sm" type="submit"><?= (int) $r['aktif'] ? 'Nonaktifkan' : 'Aktifkan' ?></button></form>
          <?php endif; ?>
        </span>
      </div>
    <?php endforeach; ?>
  </section>

  <section class="panel box">
    <h2 class="box-title">Tambah akun</h2>
    <form method="post" class="row-gap wrap"><?= csrf_field() ?>
      <input type="hidden" name="act" value="baru">
      <input class="input" name="nama" placeholder="Nama" required style="flex:1;min-width:140px">
      <input class="input" name="username" placeholder="username" required style="flex:1;min-width:120px">
      <input class="input" type="password" name="password" placeholder="Kata sandi (min. 6)" required minlength="6" style="flex:1;min-width:140px">
      <select class="input" name="role"><option value="pegawai">Pegawai</option><option value="owner">Pemilik</option></select>
      <button class="btn btn-primary btn-md" type="submit">Tambah</button>
    </form>
  </section>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
