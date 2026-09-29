<?php
/**
 * Kerangka atas setiap halaman: sidebar navigasi + pembuka <main>.
 * Sebelum include: set $page (kunci menu aktif) dan $title (judul halaman).
 */
$page  = $page  ?? 'dashboard';
$title = $title ?? 'KolegaOps';

$nav = [
    'dashboard' => 'Dashboard',
    'kasir'     => 'Kasir',
    'menu'      => 'Manajemen Menu',
    'stok'      => 'Manajemen Stok',
    'laporan'   => 'Laporan Transaksi',
];
// Kasir tampil sebagai Rina; halaman pengelolaan lain sebagai pemilik.
$user = $page === 'kasir' ? ['Rina', 'Kasir · Shift Pagi'] : ['Pak Dimas', 'Pemilik'];
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
      <?php foreach ($nav as $key => $label): ?>
        <a class="nav-item<?= $key === $page ? ' active' : '' ?>" href="<?= $key ?>.php">
          <span class="nav-mark"></span><?= e($label) ?>
        </a>
      <?php endforeach; ?>
    </nav>
    <div class="sidebar-user">
      <span class="avatar"></span>
      <div class="sidebar-user-text">
        <span class="sidebar-user-name"><?= e($user[0]) ?></span>
        <span class="sidebar-user-role"><?= e($user[1]) ?></span>
      </div>
    </div>
  </aside>
  <main class="main">
