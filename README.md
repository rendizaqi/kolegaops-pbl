# KolegaOps

Sistem kasir (POS) berbasis web untuk **Warkop Kolega**, dibuat sebagai proyek PBL.
KolegaOps membantu mencatat penjualan, mengelola menu dan stok bahan baku, serta
melihat laporan transaksi harian.

## Fitur

| Halaman | Fungsi |
| --- | --- |
| **Login** | Akun sendiri untuk tiap orang: 1 pemilik dan 5 pegawai |
| **Shift** | Buka/tutup shift (8 jam, satu kasir), modal awal, pengeluaran, selisih uang laci |
| **Kasir** | Pesanan, bayar tunai atau QRIS, open bill, kembalian otomatis, stok menu berkurang otomatis |
| **Struk** | Struk 80 mm siap cetak tanpa watermark |
| **Riwayat Transaksi** | Filter tanggal, shift, metode, status, ekspor CSV |
| **Manajemen Menu** | Nama, kategori, HPP, harga jual, profit, stok menu, isi ulang stok |
| **Stok Bahan Baku** | Status Kritis / Menipis / Aman dan update stok |
| **Laporan** | Per shift dan harian, lengkap dengan HPP dan laba untuk pemilik |
| **Dashboard & Akun** | Ringkasan pemilik, kelola akun pegawai |

Akun demo: semua memakai kata sandi `kolega123` (lihat `php/README.md`).

## Teknologi

- **PHP 8 native** (tanpa framework), **HTML**, **CSS**
- **SQLite** bawaan untuk demo, **MySQL** sebagai opsi (belum diuji pada versi ini)
- **Docker** (`php/Dockerfile` dan `php/docker-compose.yml`)

Folder `src/` berisi prototipe tampilan versi React + TypeScript + Vite. Versi ini
dipakai sebagai acuan desain, bukan aplikasi utama.

## Struktur proyek

```
php/        Aplikasi utama (PHP native)
  login, dashboard, kasir, shift, struk, riwayat, menu, stok, laporan, users
  includes/   koneksi database, fungsi bantu, kerangka halaman
  config.php  pengaturan koneksi database
  schema.sql  skema tabel MySQL
  setup.php   reset database + data contoh
  init.php    inisialisasi database yang aman diulang (dipakai Docker)
src/        Prototipe tampilan React (acuan desain)
project/    File desain wireframe
chats/      Catatan proses desain
```

## Menjalankan di komputer sendiri

Butuh PHP 8 dengan ekstensi `pdo_sqlite`. Tidak perlu memasang MySQL.

```sh
cd php
php -S localhost:8000    # lalu buka http://localhost:8000
```

Dengan Docker (aplikasi + MySQL):

```sh
cd php
docker compose up --build    # lalu buka port 80 dari service "app"
```

Untuk memakai MySQL biasa, isi kredensial di `php/config.php` (atau lewat environment
variable `KOLEGA_DB_*`), impor `php/schema.sql`, lalu jalankan `php/setup.php` sekali.

## Status

Versi demo dengan data contoh. Sudah jalan: login per akun, shift, kasir (tunai, QRIS,
open bill), struk, HPP dan laba, laporan per shift dan harian.

Belum ada, dan masuk rencana pengembangan:

- Stok bahan baku yang terhubung ke menu, dan laporan stok bahan baku per shift
- Rekap QRIS otomatis dari DANA (saat ini dicocokkan manual dengan laporan DANA)
- Pengiriman laporan ke WhatsApp
- Pengujian jalur MySQL
