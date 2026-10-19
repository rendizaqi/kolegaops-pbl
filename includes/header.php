<?php
// Kerangka atas setiap halaman: sidebar navigasi + pembuka <main>.
// Sebelum include, halaman mengisi: $page_title, $menu_aktif, dan (opsional) $pengguna.
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/data.php';

$page_title = $page_title ?? 'KolegaOps';
$menu_aktif = $menu_aktif ?? '';
$pengguna = $pengguna ?? ['nama' => 'Pak Dimas', 'peran' => 'Pemilik'];

// Prefix relatif ke root proyek ini (bukan root domain) — supaya assets dan link
// tetap benar walau proyek diakses lewat subfolder.
$__root = dirname(__DIR__);
$__folder = dirname($_SERVER['SCRIPT_FILENAME']);
$__rel = ltrim(str_replace('\\', '/', substr($__folder, strlen($__root))), '/');
$base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);

// [kunci, alamat, label]
$menuNav = [
    ['dashboard', 'index.php', 'Dashboard'],
    ['kasir', 'kasir/index.php', 'Kasir'],
    ['menu', 'menu/list.php', 'Manajemen Menu'],
    ['stok', 'stok/list.php', 'Manajemen Stok'],
    ['laporan', 'laporan/harian.php', 'Laporan Transaksi'],
];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo e($page_title); ?> | KolegaOps</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;800&display=swap">
    <link rel="stylesheet" href="<?php echo $base; ?>assets/css/style.css">
</head>
<body>
<div class="app">
    <aside class="sidebar">
        <div class="sidebar-top">
            <div class="brand">
                <div class="brand-logo">Kolega<span>Ops</span></div>
                <div class="brand-sub">Warkop Kolega</div>
            </div>
            <button type="button" id="nav-toggle-btn" class="nav-toggle" aria-label="Menu">&#9776;</button>
        </div>
        <div class="sidebar-isi" id="sidebar-isi">
            <div class="sidebar-rule"></div>
            <nav class="nav">
                <?php foreach ($menuNav as [$kunci, $alamat, $label]): ?>
                <a class="nav-item<?php echo $kunci === $menu_aktif ? ' active' : ''; ?>" href="<?php echo $base . $alamat; ?>">
                    <span class="nav-mark"></span><?php echo e($label); ?>
                </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-user">
                <span class="avatar"></span>
                <div class="sidebar-user-text">
                    <span class="sidebar-user-name"><?php echo e($pengguna['nama']); ?></span>
                    <span class="sidebar-user-role"><?php echo e($pengguna['peran']); ?></span>
                </div>
            </div>
        </div>
    </aside>
    <main class="main">
        <?php if (!empty($_GET['info'])): ?>
        <p class="alert alert-warn">Tampilan contoh: data yang Anda isi belum disimpan karena database belum dibuat.</p>
        <?php endif; ?>
