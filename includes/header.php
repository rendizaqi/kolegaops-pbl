<?php
// Dipakai halaman yang sudah meng-include auth.php (atau owner.php) dan koneksi.php.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/csrf.php';
require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/shift.php';

$pemilik = ($_SESSION['role'] ?? '') === 'owner';
$shiftBerjalan = shift_aktif($pdo);

// Pesan satu kali pakai dari halaman sebelumnya (redirect).
$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

// Prefix relatif ke root proyek ini (bukan root domain) — supaya assets, link,
// dan redirect tetap benar walau proyek diakses lewat subfolder.
$__root = dirname(__DIR__);
$__folder = dirname($_SERVER['SCRIPT_FILENAME']);
$__rel = ltrim(str_replace('\\', '/', substr($__folder, strlen($__root))), '/');
$base = $__rel === '' ? '' : str_repeat('../', substr_count($__rel, '/') + 1);

// [alamat, label, hanya pemilik?]
$menuNav = [
    ['index.php', 'Dashboard', true],
    ['kasir/index.php', 'Kasir', false],
    ['shift/index.php', 'Shift', false],
    ['transaksi/riwayat.php', 'Riwayat Transaksi', false],
    ['menu/list.php', 'Manajemen Menu', false],
    ['bahan/list.php', 'Stok Bahan Baku', false],
    ['laporan/shift.php', 'Laporan', false],
    ['pengguna/list.php', 'Akun', true],
];
$halamanIni = ltrim(str_replace('\\', '/', substr($_SERVER['SCRIPT_FILENAME'], strlen($__root))), '/');
$bagianIni = explode('/', $halamanIni)[0];

$peran = $pemilik ? 'Pemilik' : 'Pegawai';
if ($shiftBerjalan && (int) $shiftBerjalan['kasir_id'] === (int) $_SESSION['user_id']) {
    $peran .= ' · Kasir shift ' . $shiftBerjalan['nama'];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo isset($page_title) ? e($page_title) . ' | ' : ''; ?>KolegaOps</title>
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
                <?php foreach ($menuNav as [$alamat, $label, $khususPemilik]): ?>
                    <?php if ($khususPemilik && !$pemilik) continue; ?>
                    <?php $aktif = explode('/', $alamat)[0] === $bagianIni || ($alamat === 'index.php' && $bagianIni === 'index.php'); ?>
                    <a class="nav-item<?php echo $aktif ? ' active' : ''; ?>" href="<?php echo $base . $alamat; ?>">
                        <span class="nav-mark"></span><?php echo e($label); ?>
                    </a>
                <?php endforeach; ?>
            </nav>
            <div class="sidebar-shift">
                <?php if ($shiftBerjalan): ?>
                    <span class="dot dot-on"></span>
                    <span>Shift <?php echo e($shiftBerjalan['nama']); ?> berjalan<br><small><?php echo e($shiftBerjalan['kasir']); ?></small></span>
                <?php else: ?>
                    <span class="dot"></span><span>Belum ada shift aktif</span>
                <?php endif; ?>
            </div>
            <div class="sidebar-user">
                <span class="avatar"></span>
                <div class="sidebar-user-text">
                    <span class="sidebar-user-name"><?php echo e($_SESSION['nama']); ?></span>
                    <span class="sidebar-user-role"><?php echo e($peran); ?></span>
                </div>
                <form method="post" action="<?php echo $base; ?>auth/logout.php" class="logout">
                    <?php echo csrf_field(); ?>
                    <button type="submit">Keluar</button>
                </form>
            </div>
        </div>
    </aside>
    <main class="main">
        <?php if ($shiftBerjalan && $shiftBerjalan['tanggal'] < date('Y-m-d')): ?>
            <div class="banner">
                Shift <?php echo e($shiftBerjalan['nama']); ?> (<?php echo e($shiftBerjalan['kasir']); ?>) dibuka <?php echo e(tanggal_id($shiftBerjalan['tanggal'])); ?> dan belum ditutup.
                Tutup shift paling lambat pukul 00:00 agar laporan tidak tercampur.
                <a href="<?php echo $base; ?>shift/index.php">Tutup sekarang</a>
            </div>
        <?php endif; ?>
        <?php if ($flash): ?>
            <p class="alert alert-<?php echo $flash['type'] === 'success' ? 'ok' : ($flash['type'] === 'warning' ? 'warn' : 'err'); ?>"><?php echo e($flash['pesan']); ?></p>
        <?php endif; ?>
