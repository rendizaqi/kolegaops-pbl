<?php
/**
 * Fungsi bantu bersama: sesi, koneksi database, login, shift, format, laporan.
 * Setiap halaman meng-include file ini paling atas sebelum mencetak apa pun.
 */
declare(strict_types=1);

date_default_timezone_set('Asia/Jakarta');

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
    session_start();
}

$GLOBALS['KOLEGA_CONFIG'] = require __DIR__ . '/../config.php';
require_once __DIR__ . '/schema_seed.php';

/** Koneksi PDO tunggal. Pada SQLite, tabel + data contoh dibuat otomatis bila belum ada. */
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

    if ($c['driver'] === 'sqlite') {
        $has = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'")->fetchColumn();
        if (!$has) {
            kolega_create_tables($pdo, 'sqlite', true);
            kolega_seed($pdo);
        }
    }
    return $pdo;
}

// ── Format ──────────────────────────────────────────────

/** "Rp 1.234.000" */
function rp($n): string
{
    return 'Rp ' . number_format((float) round((float) $n), 0, ',', '.');
}

/** Angka desimal ala Indonesia tanpa nol berlebih: 1,5 / 120 / 0,8 */
function num_id($n): string
{
    $s = number_format((float) $n, 2, ',', '.');
    if (str_contains($s, ',')) {
        $s = rtrim(rtrim($s, '0'), ',');
    }
    return $s;
}

/** Escape untuk output HTML. */
function e($s): string
{
    return htmlspecialchars((string) $s, ENT_QUOTES, 'UTF-8');
}

/** "Selasa, 29 Sep 2026" dari 'Y-m-d' (default hari ini). */
function tgl_id(?string $ymd = null): string
{
    $ts = $ymd ? strtotime($ymd) : time();
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bln = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return $hari[(int) date('w', $ts)] . ', ' . date('j', $ts) . ' ' . $bln[(int) date('n', $ts)] . ' ' . date('Y', $ts);
}

/** Singkatan hari: Min, Sen, ... */
function hari_singkat(string $ymd): string
{
    return ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'][(int) date('w', strtotime($ymd))];
}

/** Status stok bahan baku: kritis (<= min), menipis (<= 1,5x min), selebihnya aman. */
function stok_status(float $qty, float $min): string
{
    if ($qty <= $min) {
        return 'kritis';
    }
    return $qty <= $min * 1.5 ? 'menipis' : 'aman';
}

/** Batas "stok menu tinggal sedikit". */
const STOK_MENU_RENDAH = 10;
/** Jumlah isi ulang stok menu yang biasa dipakai. */
const STOK_MENU_ISI_ULANG = 50;

function shift_names(): array
{
    return ['Pagi', 'Siang', 'Malam'];
}

// ── Navigasi & pesan ────────────────────────────────────

/** Redirect (pola Post/Redirect/Get) lalu berhenti. */
function redirect(string $url): never
{
    header('Location: ' . $url);
    exit;
}

function flash_set(string $key, string $msg): void
{
    $_SESSION['flash'][$key] = $msg;
}

/** Ambil & hapus pesan flash satu kali pakai. */
function take_flash(string $key): ?string
{
    $v = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $v;
}

// ── CSRF ────────────────────────────────────────────────

function csrf_token(): string
{
    return $_SESSION['csrf'] ??= bin2hex(random_bytes(16));
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf" value="' . e(csrf_token()) . '">';
}

/** Tolak POST tanpa token yang cocok. */
function csrf_check(): void
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!hash_equals($_SESSION['csrf'] ?? '', (string) ($_POST['csrf'] ?? ''))) {
            http_response_code(400);
            exit('Sesi formulir tidak valid. Muat ulang halaman lalu coba lagi.');
        }
    }
}

// ── Login ───────────────────────────────────────────────

/** Pengguna yang sedang login (null bila belum / akun dinonaktifkan). */
function current_user(): ?array
{
    $id = $_SESSION['uid'] ?? null;
    if (!$id) {
        return null;
    }
    $st = pdo()->prepare('SELECT id, nama, username, role, aktif FROM users WHERE id = ?');
    $st->execute([$id]);
    $u = $st->fetch();
    if (!$u || !(int) $u['aktif']) {
        unset($_SESSION['uid']);
        return null;
    }
    return $u;
}

/** Wajib login. Sekaligus memeriksa token CSRF untuk semua POST. */
function require_login(): array
{
    $u = current_user();
    if (!$u) {
        redirect('login.php');
    }
    csrf_check();
    return $u;
}

function require_owner(): array
{
    $u = require_login();
    if ($u['role'] !== 'owner') {
        http_response_code(403);
        exit('<p style="font:16px system-ui;padding:24px">Halaman ini hanya untuk pemilik. <a href="index.php">Kembali</a></p>');
    }
    return $u;
}

// ── Shift ───────────────────────────────────────────────

/** Shift yang sedang berjalan (hanya satu pada satu waktu), atau null. */
function current_shift(): ?array
{
    $st = pdo()->query("SELECT * FROM shift WHERE status = 'buka' ORDER BY id DESC LIMIT 1");
    return $st->fetch() ?: null;
}

/** Ringkasan satu shift: tunai, QRIS, total, HPP, laba kotor, pengeluaran. */
function shift_stats(PDO $db, int $id): array
{
    $r = ['tunai' => 0, 'qris' => 0, 'total' => 0, 'n' => 0, 'hpp' => 0, 'batal' => 0, 'pengeluaran' => 0];

    $st = $db->prepare("SELECT metode, COUNT(*) AS n, COALESCE(SUM(total),0) AS t, COALESCE(SUM(hpp),0) AS h
                        FROM transaksi WHERE shift_id = ? AND status = 'lunas' GROUP BY metode");
    $st->execute([$id]);
    foreach ($st->fetchAll() as $row) {
        $r[$row['metode'] === 'qris' ? 'qris' : 'tunai'] += (int) $row['t'];
        $r['total'] += (int) $row['t'];
        $r['n'] += (int) $row['n'];
        $r['hpp'] += (int) $row['h'];
    }

    $st = $db->prepare("SELECT COUNT(*) FROM transaksi WHERE shift_id = ? AND status = 'batal'");
    $st->execute([$id]);
    $r['batal'] = (int) $st->fetchColumn();

    $st = $db->prepare('SELECT COALESCE(SUM(nominal),0) FROM pengeluaran WHERE shift_id = ?');
    $st->execute([$id]);
    $r['pengeluaran'] = (int) $st->fetchColumn();

    $r['laba'] = $r['total'] - $r['hpp'];
    return $r;
}

/** Uang tunai yang seharusnya ada di laci: modal awal + penjualan tunai - pengeluaran (QRIS tidak masuk laci). */
function kas_diharapkan(array $shift, array $stats): int
{
    return (int) $shift['modal_awal'] + $stats['tunai'] - $stats['pengeluaran'];
}

// ── Pesanan ─────────────────────────────────────────────

/**
 * Simpan pesanan (status 'lunas' atau 'open') beserta itemnya.
 * Panggil di dalam transaksi database. Mengembalikan id transaksi.
 */
function save_order(PDO $db, array $shift, array $cart, array $menuAll, string $status, string $metode, int $bayar, string $meja): int
{
    $total = 0;
    $hpp = 0;
    foreach ($cart as $id => $q) {
        $total += (int) $menuAll[$id]['harga'] * $q;
        $hpp += (int) $menuAll[$id]['hpp'] * $q;
    }
    $kembali = $status === 'lunas' ? max(0, $bayar - $total) : 0;

    $db->prepare('INSERT INTO transaksi (kode, shift_id, tanggal, waktu, kasir, shift, metode, total, hpp, bayar, kembalian, meja, status)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)')
       ->execute(['', $shift['id'], date('Y-m-d'), date('H:i'), $shift['kasir'], $shift['nama'], $metode, $total, $hpp, $bayar, $kembali, $meja, $status]);
    $tid = (int) $db->lastInsertId();

    $prefix = $status === 'open' ? 'BILL-' : 'TRX-';
    $db->prepare('UPDATE transaksi SET kode = ? WHERE id = ?')
       ->execute([$prefix . str_pad((string) $tid, 4, '0', STR_PAD_LEFT), $tid]);

    $ins = $db->prepare('INSERT INTO transaksi_item (transaksi_id, menu_id, nama, qty, harga, hpp) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($cart as $id => $q) {
        $m = $menuAll[$id];
        $ins->execute([$tid, $id, $m['nama'], $q, $m['harga'], $m['hpp']]);
    }
    return $tid;
}
