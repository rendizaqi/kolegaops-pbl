# KolegaOps — versi PHP

Sistem POS Warkop Kolega dibangun dengan **PHP + HTML + CSS** dan **MySQL**.
Tanpa JavaScript: semua interaksi (keranjang kasir, kembalian, update stok,
filter laporan) berjalan lewat form PHP di sisi server.

## Halaman

| File | Halaman |
| --- | --- |
| `dashboard.php` | Ringkasan hari ini, grafik 7 hari, transaksi terbaru |
| `kasir.php` | Kasir — klik menu, atur qty, nominal bayar, kembalian, proses bayar |
| `menu.php` | Manajemen menu — tambah/edit/hapus produk, aktif/nonaktif |
| `stok.php` | Manajemen stok — badge kritis/menipis, update & tambah bahan |
| `laporan.php` | Laporan transaksi — filter tanggal/shift/kasir, ekspor CSV |

## Menjalankan di hosting (MySQL)

1. Upload folder `php/` ke hosting (mis. `public_html/kolegaops`).
2. Buat database MySQL, lalu isi kredensialnya di `config.php`
   (atau lewat environment variable `KOLEGA_DB_*`).
3. Buat tabel: impor `schema.sql` lewat phpMyAdmin.
4. Isi data contoh: buka `setup.php` sekali di browser (lalu boleh dihapus).
5. Buka `dashboard.php`.

## Menjalankan di komputer sendiri untuk uji coba

Tanpa perlu server MySQL — pakai SQLite:

```sh
cd php
KOLEGA_DB_DRIVER=sqlite php setup.php          # buat tabel + data contoh
KOLEGA_DB_DRIVER=sqlite php -S localhost:8000  # buka http://localhost:8000
```

## Struktur

- `config.php` — pengaturan koneksi database
- `schema.sql` — skema tabel MySQL
- `setup.php` — membuat tabel & mengisi data contoh (MySQL atau SQLite)
- `includes/functions.php` — koneksi PDO, format rupiah, status stok, escaping
- `includes/header.php` / `footer.php` — kerangka sidebar tiap halaman
- `styles.css` — seluruh tampilan (warna Warkop Kolega, gaya modernist)

Data awal adalah data contoh dan bisa diubah langsung dari aplikasi
(tersimpan di database).
