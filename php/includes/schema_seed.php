<?php
/**
 * Definisi tabel + data contoh, dipakai bersama oleh functions.php (SQLite otomatis),
 * setup.php (reset lokal) dan init.php (inisialisasi aman saat container jalan).
 */
declare(strict_types=1);

/** Kata sandi akun demo (semua akun). Ganti setelah dipakai sungguhan. */
const KOLEGA_DEMO_PASSWORD = 'kolega123';

/** Buat semua tabel. $ifNotExists = true untuk produksi (tidak menimpa data). */
function kolega_create_tables(PDO $db, string $driver, bool $ifNotExists): void
{
    $auto = $driver === 'sqlite'
        ? 'INTEGER PRIMARY KEY AUTOINCREMENT'
        : 'INT AUTO_INCREMENT PRIMARY KEY';
    $ine = $ifNotExists ? 'IF NOT EXISTS ' : '';

    $db->exec("CREATE TABLE {$ine}users (
        id $auto,
        nama VARCHAR(60) NOT NULL,
        username VARCHAR(30) NOT NULL UNIQUE,
        password_hash VARCHAR(255) NOT NULL,
        role VARCHAR(10) NOT NULL DEFAULT 'pegawai',
        aktif INT NOT NULL DEFAULT 1
    )");
    $db->exec("CREATE TABLE {$ine}menu (
        id $auto,
        nama VARCHAR(100) NOT NULL,
        kategori VARCHAR(30) NOT NULL,
        hpp INT NOT NULL DEFAULT 0,
        harga INT NOT NULL,
        stok INT NOT NULL DEFAULT 0,
        aktif INT NOT NULL DEFAULT 1
    )");
    $db->exec("CREATE TABLE {$ine}bahan (
        id $auto,
        nama VARCHAR(100) NOT NULL,
        qty DECIMAL(10,2) NOT NULL,
        satuan VARCHAR(20) NOT NULL,
        minimum DECIMAL(10,2) NOT NULL
    )");
    $db->exec("CREATE TABLE {$ine}shift (
        id $auto,
        nama VARCHAR(10) NOT NULL,
        kasir_id INT NOT NULL,
        kasir VARCHAR(60) NOT NULL,
        tanggal VARCHAR(10) NOT NULL,
        buka_at VARCHAR(19) NOT NULL,
        tutup_at VARCHAR(19) NULL,
        modal_awal INT NOT NULL DEFAULT 0,
        uang_fisik INT NULL,
        catatan VARCHAR(255) NULL,
        status VARCHAR(10) NOT NULL DEFAULT 'buka'
    )");
    $db->exec("CREATE TABLE {$ine}transaksi (
        id $auto,
        kode VARCHAR(20) NOT NULL,
        shift_id INT NOT NULL,
        tanggal VARCHAR(10) NOT NULL,
        waktu VARCHAR(5) NOT NULL,
        kasir VARCHAR(60) NOT NULL,
        shift VARCHAR(10) NOT NULL,
        metode VARCHAR(10) NOT NULL DEFAULT 'tunai',
        total INT NOT NULL,
        hpp INT NOT NULL DEFAULT 0,
        bayar INT NOT NULL DEFAULT 0,
        kembalian INT NOT NULL DEFAULT 0,
        meja VARCHAR(30) NOT NULL DEFAULT '',
        status VARCHAR(10) NOT NULL DEFAULT 'lunas'
    )");
    $db->exec("CREATE TABLE {$ine}transaksi_item (
        id $auto,
        transaksi_id INT NOT NULL,
        menu_id INT NOT NULL,
        nama VARCHAR(100) NOT NULL,
        qty INT NOT NULL,
        harga INT NOT NULL,
        hpp INT NOT NULL DEFAULT 0
    )");
    $db->exec("CREATE TABLE {$ine}pengeluaran (
        id $auto,
        shift_id INT NOT NULL,
        keterangan VARCHAR(100) NOT NULL,
        nominal INT NOT NULL,
        waktu VARCHAR(5) NOT NULL
    )");
    $db->exec("CREATE TABLE {$ine}ringkasan_harian (
        tanggal VARCHAR(10) PRIMARY KEY,
        hari VARCHAR(5) NOT NULL,
        total INT NOT NULL
    )");
}

/** Isi data contoh. Asumsikan tabel kosong. */
function kolega_seed(PDO $db): void
{
    $hash = password_hash(KOLEGA_DEMO_PASSWORD, PASSWORD_DEFAULT);

    // ── Akun: 1 pemilik + 5 pegawai ──
    $users = [
        ['Pak Dimas', 'dimas', 'owner'],
        ['Rina', 'rina', 'pegawai'],
        ['Andi', 'andi', 'pegawai'],
        ['Bayu', 'bayu', 'pegawai'],
        ['Citra', 'citra', 'pegawai'],
        ['Dewi', 'dewi', 'pegawai'],
    ];
    $st = $db->prepare('INSERT INTO users (nama, username, password_hash, role, aktif) VALUES (?, ?, ?, ?, 1)');
    foreach ($users as $u) {
        $st->execute([$u[0], $u[1], $hash, $u[2]]);
    }

    // ── Menu: nama, kategori, HPP, harga jual, stok, aktif ──
    $menu = [
        ['Kopi Hitam', 'Kopi', 1800, 5000, 40, 1],
        ['Kopi Susu Kolega', 'Kopi', 3500, 10000, 35, 1],
        ['Es Kopi Gula Aren', 'Kopi', 5200, 15000, 8, 1],
        ['Kopi Tubruk', 'Kopi', 2000, 6000, 30, 1],
        ['Teh Tarik', 'Non-Kopi', 3000, 10000, 25, 1],
        ['Es Teh Manis', 'Non-Kopi', 1200, 5000, 50, 1],
        ['Es Jeruk', 'Non-Kopi', 2800, 8000, 0, 0],
        ['Indomie Goreng', 'Makanan', 5500, 12000, 6, 1],
        ['Indomie Rebus Telur', 'Makanan', 6800, 14000, 20, 1],
        ['Nasi Goreng Kolega', 'Makanan', 8000, 18000, 15, 1],
        ['Roti Bakar Coklat', 'Snack', 5000, 13000, 12, 1],
        ['Pisang Goreng', 'Snack', 3500, 10000, 10, 1],
    ];
    $st = $db->prepare('INSERT INTO menu (nama, kategori, hpp, harga, stok, aktif) VALUES (?, ?, ?, ?, ?, ?)');
    foreach ($menu as $m) {
        $st->execute($m);
    }

    // ── Bahan baku ──
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

    // ── Dua shift hari ini yang sudah ditutup (shift Malam dibuka langsung saat demo) ──
    $today = date('Y-m-d');
    $shifts = [
        // nama, kasir (id akun), jam buka, jam tutup, modal, selisih uang fisik, pengeluaran, transaksi
        ['Pagi', 2, '06:00', '14:00', 200000, -2000, ['Es batu', 15000], [
            ['06:12', 'tunai', [[1, 2], [6, 1]], 'lunas'],
            ['06:40', 'qris', [[2, 1]], 'lunas'],
            ['07:05', 'tunai', [[10, 1], [1, 1], [6, 2]], 'lunas'],
            ['08:18', 'tunai', [[4, 1]], 'batal'],
            ['09:30', 'qris', [[3, 1], [12, 1]], 'lunas'],
            ['11:20', 'tunai', [[8, 1], [5, 1]], 'lunas'],
        ]],
        ['Siang', 3, '14:00', '22:00', 250000, 0, ['Gas LPG', 20000], [
            ['14:22', 'tunai', [[9, 1], [2, 2]], 'lunas'],
            ['15:10', 'qris', [[10, 2]], 'lunas'],
            ['16:45', 'tunai', [[11, 1], [3, 1]], 'lunas'],
            ['18:30', 'qris', [[8, 2], [6, 2]], 'lunas'],
            ['20:05', 'tunai', [[2, 1], [12, 2]], 'lunas'],
        ]],
    ];
    $names = ['', 'Pak Dimas', 'Rina', 'Andi', 'Bayu', 'Citra', 'Dewi'];

    $insShift = $db->prepare('INSERT INTO shift (nama, kasir_id, kasir, tanggal, buka_at, tutup_at, modal_awal, uang_fisik, catatan, status)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insTrx = $db->prepare('INSERT INTO transaksi (kode, shift_id, tanggal, waktu, kasir, shift, metode, total, hpp, bayar, kembalian, meja, status)
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)');
    $insItem = $db->prepare('INSERT INTO transaksi_item (transaksi_id, menu_id, nama, qty, harga, hpp) VALUES (?, ?, ?, ?, ?, ?)');
    $insOut = $db->prepare('INSERT INTO pengeluaran (shift_id, keterangan, nominal, waktu) VALUES (?, ?, ?, ?)');

    $no = 0;
    foreach ($shifts as [$nama, $kid, $jb, $jt, $modal, $selisih, $out, $trxs]) {
        $kasir = $names[$kid];
        $cash = 0;
        $trxRows = [];
        foreach ($trxs as [$waktu, $metode, $items, $status]) {
            $total = 0;
            $hpp = 0;
            foreach ($items as [$mi, $q]) {
                $total += $menu[$mi - 1][3] * $q;
                $hpp += $menu[$mi - 1][2] * $q;
            }
            if ($status === 'lunas' && $metode === 'tunai') {
                $cash += $total;
            }
            $trxRows[] = [$waktu, $metode, $items, $status, $total, $hpp];
        }
        $expected = $modal + $cash - $out[1];

        $insShift->execute([$nama, $kid, $kasir, $today, "$today $jb:00", "$today $jt:00", $modal, $expected + $selisih,
            $selisih ? 'Uang laci kurang, dicek ulang besok.' : null, 'tutup']);
        $sid = (int) $db->lastInsertId();
        $insOut->execute([$sid, $out[0], $out[1], $jb]);

        foreach ($trxRows as [$waktu, $metode, $items, $status, $total, $hpp]) {
            $no++;
            $bayar = $metode === 'qris' ? $total : (int) (ceil($total / 10000) * 10000);
            $insTrx->execute([sprintf('TRX-%04d', $no), $sid, $today, $waktu, $kasir, $nama, $metode, $total, $hpp,
                $bayar, $bayar - $total, 'Meja ' . (($no % 6) + 1), $status]);
            $tid = (int) $db->lastInsertId();
            foreach ($items as [$mi, $q]) {
                $insItem->execute([$tid, $mi, $menu[$mi - 1][0], $q, $menu[$mi - 1][3], $menu[$mi - 1][2]]);
            }
        }
    }

    // ── Penjualan 6 hari sebelumnya (hari ini dihitung langsung dari transaksi) ──
    $week = [2310000, 2580000, 3420000, 3890000, 3150000, 2540000];
    $hari = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    $st = $db->prepare('INSERT INTO ringkasan_harian (tanggal, hari, total) VALUES (?, ?, ?)');
    foreach ($week as $i => $total) {
        $ts = strtotime('-' . (6 - $i) . ' days');
        $st->execute([date('Y-m-d', $ts), $hari[(int) date('w', $ts)], $total]);
    }
}
