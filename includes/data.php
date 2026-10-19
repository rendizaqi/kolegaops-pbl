<?php
// DATA CONTOH untuk tampilan. Belum ada database: semua data di bawah ini
// hanya array PHP biasa, jadi perubahan di halaman tidak tersimpan.
// Nanti array ini diganti dengan hasil query database.

date_default_timezone_set('Asia/Jakarta');

// Menu: id, nama, kategori, HPP (modal per porsi), harga jual, stok siap jual, aktif
$menu = [
    ['id' => 1,  'nama' => 'Kopi Hitam',          'kategori' => 'Kopi',     'hpp' => 1800, 'harga' => 5000,  'stok' => 40, 'aktif' => true],
    ['id' => 2,  'nama' => 'Kopi Susu Kolega',    'kategori' => 'Kopi',     'hpp' => 3500, 'harga' => 10000, 'stok' => 35, 'aktif' => true],
    ['id' => 3,  'nama' => 'Es Kopi Gula Aren',   'kategori' => 'Kopi',     'hpp' => 5200, 'harga' => 15000, 'stok' => 8,  'aktif' => true],
    ['id' => 4,  'nama' => 'Kopi Tubruk',         'kategori' => 'Kopi',     'hpp' => 2000, 'harga' => 6000,  'stok' => 30, 'aktif' => true],
    ['id' => 5,  'nama' => 'Teh Tarik',           'kategori' => 'Non-Kopi', 'hpp' => 3000, 'harga' => 10000, 'stok' => 25, 'aktif' => true],
    ['id' => 6,  'nama' => 'Es Teh Manis',        'kategori' => 'Non-Kopi', 'hpp' => 1200, 'harga' => 5000,  'stok' => 50, 'aktif' => true],
    ['id' => 7,  'nama' => 'Es Jeruk',            'kategori' => 'Non-Kopi', 'hpp' => 2800, 'harga' => 8000,  'stok' => 0,  'aktif' => false],
    ['id' => 8,  'nama' => 'Indomie Goreng',      'kategori' => 'Makanan',  'hpp' => 5500, 'harga' => 12000, 'stok' => 6,  'aktif' => true],
    ['id' => 9,  'nama' => 'Indomie Rebus Telur', 'kategori' => 'Makanan',  'hpp' => 6800, 'harga' => 14000, 'stok' => 20, 'aktif' => true],
    ['id' => 10, 'nama' => 'Nasi Goreng Kolega',  'kategori' => 'Makanan',  'hpp' => 8000, 'harga' => 18000, 'stok' => 15, 'aktif' => true],
    ['id' => 11, 'nama' => 'Roti Bakar Coklat',   'kategori' => 'Snack',    'hpp' => 5000, 'harga' => 13000, 'stok' => 12, 'aktif' => true],
    ['id' => 12, 'nama' => 'Pisang Goreng',       'kategori' => 'Snack',    'hpp' => 3500, 'harga' => 10000, 'stok' => 10, 'aktif' => true],
];

// Bahan baku: nama, stok saat ini, satuan, batas minimum
$bahan = [
    ['nama' => 'Biji kopi robusta', 'qty' => 1.2, 'satuan' => 'kg',      'minimum' => 2],
    ['nama' => 'Biji kopi arabika', 'qty' => 3.5, 'satuan' => 'kg',      'minimum' => 1.5],
    ['nama' => 'Susu kental manis', 'qty' => 14,  'satuan' => 'kaleng',  'minimum' => 10],
    ['nama' => 'Gula pasir',        'qty' => 8,   'satuan' => 'kg',      'minimum' => 3],
    ['nama' => 'Gula aren cair',    'qty' => 0.8, 'satuan' => 'liter',   'minimum' => 1],
    ['nama' => 'Teh celup',         'qty' => 120, 'satuan' => 'pcs',     'minimum' => 50],
    ['nama' => 'Indomie goreng',    'qty' => 18,  'satuan' => 'bungkus', 'minimum' => 24],
    ['nama' => 'Telur ayam',        'qty' => 30,  'satuan' => 'butir',   'minimum' => 20],
    ['nama' => 'Roti tawar',        'qty' => 5,   'satuan' => 'bungkus', 'minimum' => 4],
    ['nama' => 'Es batu',           'qty' => 6,   'satuan' => 'kg',      'minimum' => 10],
];

// Transaksi hari ini. item = [id menu, jumlah]. Total dan HPP dihitung dari menu di bawah.
$transaksi = [
    ['kode' => 'TRX-0101', 'waktu' => '06:12', 'shift' => 'Pagi',  'kasir' => 'Rina', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[1, 2], [6, 1]]],
    ['kode' => 'TRX-0102', 'waktu' => '06:40', 'shift' => 'Pagi',  'kasir' => 'Rina', 'metode' => 'qris',  'status' => 'lunas', 'item' => [[2, 1]]],
    ['kode' => 'TRX-0103', 'waktu' => '07:05', 'shift' => 'Pagi',  'kasir' => 'Rina', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[10, 1], [1, 1], [6, 2]]],
    ['kode' => 'TRX-0104', 'waktu' => '08:18', 'shift' => 'Pagi',  'kasir' => 'Rina', 'metode' => 'tunai', 'status' => 'batal', 'item' => [[4, 1]]],
    ['kode' => 'TRX-0105', 'waktu' => '09:30', 'shift' => 'Pagi',  'kasir' => 'Rina', 'metode' => 'qris',  'status' => 'lunas', 'item' => [[3, 1], [12, 1]]],
    ['kode' => 'TRX-0106', 'waktu' => '11:20', 'shift' => 'Pagi',  'kasir' => 'Rina', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[8, 1], [5, 1]]],
    ['kode' => 'TRX-0107', 'waktu' => '14:22', 'shift' => 'Siang', 'kasir' => 'Andi', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[9, 1], [2, 2]]],
    ['kode' => 'TRX-0108', 'waktu' => '15:10', 'shift' => 'Siang', 'kasir' => 'Andi', 'metode' => 'qris',  'status' => 'lunas', 'item' => [[10, 2]]],
    ['kode' => 'TRX-0109', 'waktu' => '16:45', 'shift' => 'Siang', 'kasir' => 'Andi', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[11, 1], [3, 1]]],
    ['kode' => 'TRX-0110', 'waktu' => '18:30', 'shift' => 'Siang', 'kasir' => 'Andi', 'metode' => 'qris',  'status' => 'lunas', 'item' => [[8, 2], [6, 2]]],
    ['kode' => 'TRX-0111', 'waktu' => '20:05', 'shift' => 'Siang', 'kasir' => 'Andi', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[2, 1], [12, 2]]],
    ['kode' => 'TRX-0112', 'waktu' => '22:10', 'shift' => 'Malam', 'kasir' => 'Bayu', 'metode' => 'tunai', 'status' => 'lunas', 'item' => [[11, 1], [12, 1], [4, 1]]],
    ['kode' => 'TRX-0113', 'waktu' => '23:05', 'shift' => 'Malam', 'kasir' => 'Bayu', 'metode' => 'qris',  'status' => 'lunas', 'item' => [[3, 1]]],
];

// Hitung total & HPP tiap transaksi dari daftar item, dan lengkapi nama/harga item untuk struk.
$daftarMenuById = [];
foreach ($menu as $m) {
    $daftarMenuById[$m['id']] = $m;
}
foreach ($transaksi as $i => $t) {
    $total = 0;
    $hpp = 0;
    $rincian = [];
    foreach ($t['item'] as [$idMenu, $qty]) {
        $m = $daftarMenuById[$idMenu];
        $total += $m['harga'] * $qty;
        $hpp += $m['hpp'] * $qty;
        $rincian[] = ['nama' => $m['nama'], 'qty' => $qty, 'harga' => $m['harga']];
    }
    $transaksi[$i]['total'] = $total;
    $transaksi[$i]['hpp'] = $hpp;
    $transaksi[$i]['rincian'] = $rincian;
}

// Ringkasan per shift: modal awal, pengeluaran, dan uang tunai fisik di laci saat tutup shift.
$shiftRingkas = [
    ['shift' => 'Pagi',  'kasir' => 'Rina', 'modal' => 200000, 'pengeluaran' => 15000, 'uang_fisik' => 253000, 'catatan' => 'Es batu'],
    ['shift' => 'Siang', 'kasir' => 'Andi', 'modal' => 250000, 'pengeluaran' => 20000, 'uang_fisik' => 322000, 'catatan' => 'Gas LPG'],
    ['shift' => 'Malam', 'kasir' => 'Bayu', 'modal' => 322000, 'pengeluaran' => 0,     'uang_fisik' => 349000, 'catatan' => ''],
];

// Penjualan 6 hari sebelumnya untuk grafik dashboard (hari ini dihitung dari transaksi).
$penjualanEnamHari = [2310000, 2580000, 3420000, 3890000, 3150000, 2540000];
