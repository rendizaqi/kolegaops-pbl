# KolegaOps

Sistem kasir (POS) berbasis web untuk **Warkop Kolega**, dibuat sebagai proyek PBL.
KolegaOps membantu mencatat penjualan, mengelola menu dan stok bahan baku, serta
melihat laporan transaksi harian.

## Fitur

| Halaman | Fungsi |
| --- | --- |
| **Dashboard** | Ringkasan penjualan hari ini, jumlah transaksi, stok kritis, grafik pendapatan 7 hari, dan transaksi terbaru |
| **Kasir** | Pilih menu, atur jumlah pesanan, hitung total, input nominal bayar, kembalian otomatis, dan simpan transaksi |
| **Manajemen Menu** | Tambah, ubah, hapus produk (nama, kategori, harga), serta aktif/nonaktifkan menu |
| **Manajemen Stok** | Daftar bahan baku dengan status Kritis / Menipis / Aman, update stok (tambah, kurangi, set jumlah), dan tambah bahan |
| **Laporan Transaksi** | Filter per tanggal, shift, dan kasir; ringkasan pendapatan; ekspor CSV |

Status stok: **Kritis** bila stok di bawah atau sama dengan batas minimum, **Menipis**
bila masih dalam 1,5 kali batas minimum, selain itu **Aman**.

## Teknologi

- **PHP 8** (tanpa framework), **HTML**, **CSS**, tanpa JavaScript
- **MySQL** untuk produksi, **SQLite** untuk uji coba lokal
- **Docker** (`php/Dockerfile` dan `php/docker-compose.yml`)

Folder `src/` berisi prototipe tampilan versi React + TypeScript + Vite. Versi ini
dipakai sebagai acuan desain, bukan aplikasi utama.

## Struktur proyek

```
php/        Aplikasi utama (PHP + MySQL)
  dashboard.php, kasir.php, menu.php, stok.php, laporan.php
  includes/   koneksi database, fungsi bantu, kerangka halaman
  config.php  pengaturan koneksi database
  schema.sql  skema tabel MySQL
  setup.php   reset database lokal + data contoh
  init.php    inisialisasi database yang aman diulang (dipakai Docker)
src/        Prototipe tampilan React (acuan desain)
project/    File desain wireframe
chats/      Catatan proses desain
```

## Menjalankan di komputer sendiri

Butuh PHP 8 dengan ekstensi `pdo_sqlite`. Tidak perlu memasang MySQL.

```sh
cd php
KOLEGA_DB_DRIVER=sqlite php setup.php            # buat tabel + data contoh
KOLEGA_DB_DRIVER=sqlite php -S localhost:8000    # lalu buka http://localhost:8000
```

Dengan Docker (aplikasi + MySQL):

```sh
cd php
docker compose up --build    # lalu buka port 80 dari service "app"
```

Untuk memakai MySQL biasa, isi kredensial di `php/config.php` (atau lewat environment
variable `KOLEGA_DB_*`), impor `php/schema.sql`, lalu jalankan `php/setup.php` sekali.

## Status

Ini versi pertama dengan data contoh. Yang sudah jalan: kelima halaman di atas dengan
data tersimpan di database.

Belum ada, dan masuk rencana pengembangan:

- Login dengan akun per orang (1 pemilik, 5 pegawai)
- Pencatatan shift (8 jam), tutup shift, dan laporan per shift
- HPP dan laba per produk, laporan harian lengkap dengan HPP
- Pembayaran tunai dan QRIS, pencatatan pengeluaran
- Stok per menu yang berkurang otomatis saat transaksi
- Cetak struk dan open bill
- Pengiriman laporan ke WhatsApp
