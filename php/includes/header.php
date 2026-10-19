<?php
/**
 * Kerangka atas setiap halaman: sidebar navigasi + pembuka <main>.
 * Sebelum include: panggil require_login() / require_owner(), lalu set
 * $page (kunci menu aktif) dan $title (judul halaman).
 */
$page  = $page  ?? 'dashboard';
$title = $title ?? 'KolegaOps';
$u = current_user();
$isOwner = $u && $u['role'] === 'owner';
$shiftAktif = current_shift();

// [file, label, hanya pemilik?]
$nav = [
    ['dashboard', 'Dashboard', true],
    ['kasir', 'Kasir', false],
    ['shift', 'Shift', false],
    ['riwayat', 'Riwayat Transaksi', false],
    ['menu', 'Manajemen Menu', false],
    ['stok', 'Stok Bahan Baku', false],
    ['laporan', 'Laporan', false],
    ['users', 'Akun', true],
];

$roleLabel = $isOwner ? 'Pemilik' : 'Pegawai';
if ($shiftAktif && $u && (int) $shiftAktif['kasir_id'] === (int) $u['id']) {
    $roleLabel .= ' · Kasir shift ' . $shiftAktif['nama'];
}
?><!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($title) ?> · KolegaOps</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;800&display=swap">
<link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo">Kolega<span>Ops</span></div>
      <div class="brand-sub">Warkop Kolega</div>
    </div>
    <div class="sidebar-rule"></div>
    <nav class="nav">
      <?php foreach ($nav as [$key, $label, $ownerOnly]): ?>
        <?php if ($ownerOnly && !$isOwner) continue; ?>
        <a class="nav-item<?= $key === $page ? ' active' : '' ?>" href="<?= $key ?>.php">
          <span class="nav-mark"></span><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-shift">
      <?php if ($shiftAktif): ?>
        <span class="dot dot-on"></span>
        <span>Shift <?= e($shiftAktif['nama']) ?> berjalan<br><small><?= e($shiftAktif['kasir']) ?></small></span>
      <?php else: ?>
        <span class="dot"></span><span>Belum ada shift aktif</span>
      <?php endif; ?>
    </div>
    <div class="sidebar-user">
      <span class="avatar"></span>
      <div class="sidebar-user-text">
        <span class="sidebar-user-name"><?= e($u['nama'] ?? '') ?></span>
        <span class="sidebar-user-role"><?= e($roleLabel) ?></span>
      </div>
      <form method="post" action="logout.php" class="logout">
        <?= csrf_field() ?>
        <button type="submit" title="Keluar">Keluar</button>
      </form>
    </div>
  </aside>
  <main class="main">
    <?php if ($shiftAktif && $shiftAktif['tanggal'] < date('Y-m-d')): ?>
      <div class="banner">
        Shift <?= e($shiftAktif['nama']) ?> (<?= e($shiftAktif['kasir']) ?>) dibuka <?= e(tgl_id($shiftAktif['tanggal'])) ?> dan belum ditutup.
        Tutup shift maksimal pukul 00:00 agar laporan tidak tercampur. <a href="shift.php">Tutup sekarang</a>
      </div>
    <?php endif; ?>
    <?php if ($m = take_flash('ok')): ?><div class="alert alert-ok"><?= e($m) ?></div><?php endif; ?>
    <?php if ($m = take_flash('err')): ?><div class="alert alert-err"><?= e($m) ?></div><?php endif; ?>
