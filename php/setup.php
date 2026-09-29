<?php
/**
 * RESET lokal: hapus tabel lama, buat ulang, isi data contoh.
 * Untuk uji coba di komputer sendiri. Jangan dipakai di produksi
 * (dipakai di server = data asli terhapus). Di container, pemasangan
 * tabel ditangani init.php secara aman.
 *
 *   php setup.php               (MySQL, sesuai config.php)
 *   KOLEGA_DB_DRIVER=sqlite php setup.php
 */
require __DIR__ . '/includes/functions.php';
require __DIR__ . '/includes/schema_seed.php';

$db     = pdo();
$driver = $GLOBALS['KOLEGA_CONFIG']['driver'];

foreach (['transaksi', 'ringkasan_harian', 'bahan', 'menu'] as $t) {
    $db->exec("DROP TABLE IF EXISTS $t");
}
kolega_create_tables($db, $driver, false);
kolega_seed($db);

$msg = 'Setup selesai: tabel dibuat dan data contoh terisi.';
echo PHP_SAPI === 'cli'
    ? $msg . "\n"
    : '<p style="font:16px system-ui;padding:24px">' . e($msg) . ' <a href="dashboard.php">Buka aplikasi</a></p>';
