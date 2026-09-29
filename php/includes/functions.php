<?php
/**
 * Fungsi bantu bersama: koneksi database, format rupiah, status stok, escaping.
 * Setiap halaman meng-include file ini paling atas sebelum mencetak apa pun.
 */
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$GLOBALS['KOLEGA_CONFIG'] = require __DIR__ . '/../config.php';

/** Koneksi PDO tunggal (dibuat sekali per request). */
function pdo(): PDO
{
    static $pdo = null;
    if ($pdo instanceof PDO) {
        return $pdo;
    }
    $c = $GLOBALS['KOLEGA_CONFIG'];
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

/** Format angka jadi "Rp 1.234.000". */
function rp($n): string
{
    return 'Rp ' . number_format((float) round((float) $n), 0, ',', '.');
}

/** Format angka desimal ala Indonesia tanpa nol berlebih: 1,5 / 120 / 0,8. */
function num_id($n): string
{
    $s = number_format((float) $n, 2, ',', '.');
    if (str_contains($s, ',')) {
        $s = rtrim(rtrim($s, '0'), ',');
    }
    return $s;
}

/** Status stok: kritis (<= min), menipis (<= 1,5x min), selebihnya aman. */
function stok_status(float $qty, float $min): string
{
    if ($qty <= $min) {
        return 'kritis';
    }
    if ($qty <= $min * 1.5) {
        return 'menipis';
    }
    return 'aman';
}

/** Escape untuk output HTML. */
function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** Redirect (pola Post/Redirect/Get) lalu berhenti. */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

/** Ambil & hapus pesan flash satu kali pakai. */
function take_flash(string $key): ?string
{
    $v = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $v;
}
