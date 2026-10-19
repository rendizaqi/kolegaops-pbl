<?php
/**
 * Inisialisasi database yang AMAN diulang, dipanggil otomatis saat container
 * pertama kali jalan (lihat docker-entrypoint.sh):
 *   - membuat tabel bila belum ada (data lama tidak tersentuh),
 *   - mengisi data contoh HANYA bila tabel menu masih kosong.
 * Menunggu database siap dengan beberapa kali percobaan koneksi.
 */
declare(strict_types=1);

require_once __DIR__ . '/includes/schema_seed.php';
$c = require __DIR__ . '/config.php';

function kolega_connect(array $c): PDO
{
    if ($c['driver'] === 'sqlite') {
        $dir = dirname($c['sqlite_path']);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $pdo = new PDO('sqlite:' . $c['sqlite_path']);
    } else {
        $dsn = "mysql:host={$c['host']};port={$c['port']};dbname={$c['name']};charset=utf8mb4";
        $pdo = new PDO($dsn, $c['user'], $c['pass']);
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
}

// Tunggu database siap (maks. ~60 detik) — MySQL kadang butuh waktu menyala.
$db = null;
for ($i = 1; $i <= 30; $i++) {
    try {
        $db = kolega_connect($c);
        break;
    } catch (PDOException $ex) {
        fwrite(STDERR, "init.php: menunggu database (percobaan $i)…\n");
        sleep(2);
    }
}
if (!$db) {
    fwrite(STDERR, "init.php: database belum siap, lanjut tanpa inisialisasi.\n");
    exit(0); // biar web server tetap menyala
}

kolega_create_tables($db, $c['driver'], true);

$kosong = (int) $db->query('SELECT COUNT(*) FROM menu')->fetchColumn() === 0;
if ($kosong) {
    kolega_seed($db);
    fwrite(STDOUT, "init.php: tabel dibuat & data contoh terisi.\n");
} else {
    fwrite(STDOUT, "init.php: tabel sudah ada, data dipertahankan.\n");
}
