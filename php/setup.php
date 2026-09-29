<?php
/**
 * Membuat tabel dan mengisi data contoh. Jalankan sekali di awal:
 *   - dari terminal:  php setup.php
 *   - dari browser:   buka setup.php lalu hapus/abaikan setelah selesai.
 * Aman diulang: tabel lama di-drop dan dibuat ulang.
 */
require __DIR__ . '/includes/functions.php';

$db      = pdo();
$driver  = $GLOBALS['KOLEGA_CONFIG']['driver'];
$autoId  = $driver === 'sqlite'
    ? 'INTEGER PRIMARY KEY AUTOINCREMENT'
    : 'INT AUTO_INCREMENT PRIMARY KEY';

foreach (['transaksi', 'ringkasan_harian', 'bahan', 'menu'] as $t) {
    $db->exec("DROP TABLE IF EXISTS $t");
}

$db->exec("CREATE TABLE menu (
    id $autoId,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    harga INT NOT NULL,
    aktif INT NOT NULL DEFAULT 1
)");

$db->exec("CREATE TABLE bahan (
    id $autoId,
    nama VARCHAR(100) NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    minimum DECIMAL(10,2) NOT NULL
)");

$db->exec("CREATE TABLE transaksi (
    id $autoId,
    kode VARCHAR(20) NOT NULL,
    waktu VARCHAR(5) NOT NULL,
    tanggal VARCHAR(10) NOT NULL,
    kasir VARCHAR(50) NOT NULL,
    shift VARCHAR(10) NOT NULL,
    total INT NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'lunas'
)");

$db->exec("CREATE TABLE ringkasan_harian (
    tanggal VARCHAR(10) PRIMARY KEY,
    hari VARCHAR(5) NOT NULL,
    total INT NOT NULL
)");

// ── Menu ────────────────────────────────────────────────
$menu = [
    ['Kopi Hitam', 'Kopi', 5000, 1],
    ['Kopi Susu Kolega', 'Kopi', 10000, 1],
    ['Es Kopi Gula Aren', 'Kopi', 15000, 1],
    ['Kopi Tubruk', 'Kopi', 6000, 1],
    ['Teh Tarik', 'Non-Kopi', 10000, 1],
    ['Es Teh Manis', 'Non-Kopi', 5000, 1],
    ['Es Jeruk', 'Non-Kopi', 8000, 0],
    ['Indomie Goreng', 'Makanan', 12000, 1],
    ['Indomie Rebus Telur', 'Makanan', 14000, 1],
    ['Nasi Goreng Kolega', 'Makanan', 18000, 1],
    ['Roti Bakar Coklat', 'Snack', 13000, 1],
    ['Pisang Goreng', 'Snack', 10000, 1],
];
$st = $db->prepare('INSERT INTO menu (nama, kategori, harga, aktif) VALUES (?, ?, ?, ?)');
foreach ($menu as $m) {
    $st->execute($m);
}

// ── Bahan baku ─────────────────────────────────────────
$bahan = [
    ['Biji kopi robusta', 1.2, 'kg', 2],
    ['Biji kopi arabika', 3.5, 'kg', 1.5],
    ['Susu kental manis', 14, 'kaleng', 10],
    ['Gula pasir', 8, 'kg', 3],
    ['Gula aren cair', 0.8, 'liter', 1],
    ['Teh celup', 120, 'pcs', 50],
    ['Indomie goreng', 18, 'bungkus', 24],
    ['Telur ayam', 30, 'butir', 20],
    ['Roti tawar', 5, 'bungkus', 4],
    ['Es batu', 6, 'kg', 10],
];
$st = $db->prepare('INSERT INTO bahan (nama, qty, satuan, minimum) VALUES (?, ?, ?, ?)');
foreach ($bahan as $b) {
    $st->execute($b);
}

// ── Transaksi hari ini ─────────────────────────────────
$today = date('Y-m-d');
$trx = [
    ['06:12', 'Rina', 'Pagi', 23000, 'lunas'],
    ['06:40', 'Rina', 'Pagi', 15000, 'lunas'],
    ['07:05', 'Rina', 'Pagi', 42000, 'lunas'],
    ['08:18', 'Rina', 'Pagi', 10000, 'batal'],
    ['09:30', 'Rina', 'Pagi', 31000, 'lunas'],
    ['11:02', 'Andi', 'Siang', 56000, 'lunas'],
    ['12:15', 'Andi', 'Siang', 28000, 'lunas'],
    ['13:40', 'Andi', 'Siang', 18000, 'lunas'],
    ['14:22', 'Andi', 'Siang', 64000, 'lunas'],
    ['18:05', 'Bayu', 'Malam', 37000, 'lunas'],
    ['19:30', 'Bayu', 'Malam', 48000, 'lunas'],
    ['21:10', 'Bayu', 'Malam', 22000, 'batal'],
    ['22:45', 'Bayu', 'Malam', 71000, 'lunas'],
];
$st = $db->prepare('INSERT INTO transaksi (kode, waktu, tanggal, kasir, shift, total, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
foreach ($trx as $i => $t) {
    $kode = 'TRX-' . str_pad((string) (101 + $i), 4, '0', STR_PAD_LEFT);
    $st->execute([$kode, $t[0], $today, $t[1], $t[2], $t[3], $t[4]]);
}

// ── Ringkasan 7 hari terakhir (untuk grafik dashboard) ──
$week = [2310000, 2580000, 3420000, 3890000, 3150000, 2540000, 2845000];
$hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
$st = $db->prepare('INSERT INTO ringkasan_harian (tanggal, hari, total) VALUES (?, ?, ?)');
foreach ($week as $i => $total) {
    $ts   = strtotime("-" . (6 - $i) . " days");
    $tgl  = date('Y-m-d', $ts);
    $nama = $hari[(int) date('w', $ts)];
    $st->execute([$tgl, $nama, $total]);
}

$msg = 'Setup selesai: tabel dibuat dan data contoh terisi. Buka dashboard.php';
if (PHP_SAPI === 'cli') {
    echo $msg . "\n";
} else {
    echo '<p style="font:16px system-ui;padding:24px">' . e($msg)
        . ' &rarr; <a href="dashboard.php">Buka aplikasi</a></p>';
}
