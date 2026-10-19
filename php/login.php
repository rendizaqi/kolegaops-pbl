<?php
require __DIR__ . '/includes/functions.php';

if (current_user()) {
    redirect('index.php');
}
$db = pdo();
$err = '';
$pilih = (int) ($_POST['user'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_check();
    $st = $db->prepare('SELECT * FROM users WHERE id = ? AND aktif = 1');
    $st->execute([$pilih]);
    $row = $st->fetch();
    if ($row && password_verify((string) ($_POST['password'] ?? ''), $row['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['uid'] = (int) $row['id'];
        // Keranjang kasir tidak ikut berpindah ke orang lain.
        unset($_SESSION['cart'], $_SESSION['bayar'], $_SESSION['metode'], $_SESSION['meja']);
        redirect('index.php');
    }
    $err = 'Akun atau kata sandi salah.';
}

$users = $db->query('SELECT id, nama, role FROM users WHERE aktif = 1 ORDER BY id')->fetchAll();
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Masuk · KolegaOps</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;800&display=swap">
<link rel="stylesheet" href="styles.css">
</head>
<body class="login-body">
<main class="login">
  <div class="login-brand">
    <div class="brand-logo">Kolega<span>Ops</span></div>
    <div class="brand-sub">Warkop Kolega</div>
  </div>
  <form method="post" class="login-card">
    <?= csrf_field() ?>
    <h1 class="login-title">Masuk</h1>
    <p class="muted" style="margin:0;font-size:13px">Pilih akun sesuai orang yang bertugas di kasir.</p>
    <?php if ($err): ?><div class="alert alert-err" style="margin:0"><?= e($err) ?></div><?php endif; ?>
    <label class="field">
      <span class="label">Akun</span>
      <select class="input input-strong" name="user" required>
        <?php foreach ($users as $x): ?>
          <option value="<?= (int) $x['id'] ?>" <?= $pilih === (int) $x['id'] ? 'selected' : '' ?>>
            <?= e($x['nama']) ?> (<?= $x['role'] === 'owner' ? 'Pemilik' : 'Pegawai' ?>)
          </option>
        <?php endforeach; ?>
      </select>
    </label>
    <label class="field">
      <span class="label">Kata sandi</span>
      <input class="input input-strong" type="password" name="password" required autofocus>
    </label>
    <button class="btn btn-primary" type="submit" style="height:48px;justify-content:space-between">
      <span>Masuk</span><span>→</span>
    </button>
    <div class="demo-hint">Demo: semua akun memakai kata sandi <b><?= e(KOLEGA_DEMO_PASSWORD) ?></b></div>
  </form>
</main>
</body>
</html>
