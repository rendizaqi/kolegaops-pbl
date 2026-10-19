# KolegaOps — versi PHP native

Sistem kasir Warkop Kolega dengan **PHP native** (tanpa framework), HTML, dan CSS.
JavaScript hanya dipakai untuk tombol *Cetak struk* dan konfirmasi hapus menu.
Database bawaan **SQLite**, jadi bisa langsung dijalankan tanpa memasang MySQL.

## Menjalankan (demo)

Butuh PHP 8 dengan ekstensi `pdo_sqlite`.

```sh
cd php
php -S localhost:8000
```

Buka http://localhost:8000. Database dan data contoh dibuat otomatis saat halaman
pertama dibuka. Untuk mengembalikan data contoh: `php setup.php`.

## Akun demo

Semua akun memakai kata sandi **kolega123**.

| Akun | Peran |
| --- | --- |
| Pak Dimas | Pemilik (semua halaman, HPP, laba, kelola akun) |
| Rina, Andi, Bayu, Citra, Dewi | Pegawai (kasir, shift, riwayat, stok, laporan per shift) |

## Alur demo yang disarankan

1. Masuk sebagai **Bayu** → menu **Shift** → pilih *Malam*, isi modal awal → **Buka shift**.
2. **Kasir**: klik menu, bayar **Tunai** (kembalian otomatis) lalu **Proses Bayar** → buka **struk**.
3. Pesanan lain: pilih **QRIS** (total muncul untuk dibayar), atau **Simpan open bill** lalu **Lanjutkan**.
4. Menu **Shift**: catat **pengeluaran** (mis. es batu), lalu **Tutup shift** dengan mengisi uang fisik di laci → muncul laporan shift dengan selisih.
5. **Keluar**, masuk sebagai **Pak Dimas** → Dashboard, **Laporan → Harian** (HPP dan laba), **Manajemen Menu** (HPP, harga jual, profit, isi ulang stok), **Akun**.

## Halaman

| File | Fungsi |
| --- | --- |
| `login.php` | Masuk dengan akun masing-masing, `logout.php` untuk keluar |
| `dashboard.php` | Pemilik: penjualan, laba kotor, stok menipis, grafik 7 hari |
| `kasir.php` | Pesanan, tunai/QRIS, open bill, stok menu berkurang otomatis |
| `shift.php` | Buka/tutup shift, modal awal, pengeluaran, selisih uang laci |
| `struk.php` | Struk 80 mm siap cetak, tanpa watermark |
| `riwayat.php` | Riwayat transaksi dengan filter dan ekspor CSV |
| `menu.php` | Nama, kategori, HPP, harga jual, profit, stok menu (+50 cepat) |
| `stok.php` | Stok bahan baku (Kritis / Menipis / Aman) |
| `laporan.php` | Laporan per shift dan harian (pemilik: dengan HPP dan laba) |
| `users.php` | Pemilik: tambah akun, ganti kata sandi, nonaktifkan |

## Struktur

- `config.php` — koneksi database (SQLite bawaan, MySQL lewat `KOLEGA_DB_*`)
- `includes/functions.php` — sesi, login, CSRF, shift, laporan, format rupiah
- `includes/schema_seed.php` — tabel dan data contoh
- `includes/header.php`, `footer.php` — kerangka sidebar
- `schema.sql` — skema MySQL, `init.php` / `setup.php` — inisialisasi database
- `Dockerfile`, `docker-compose.yml` — opsi menjalankan dengan Docker + MySQL

## MySQL

Set `KOLEGA_DB_DRIVER=mysql` dan isi `KOLEGA_DB_HOST`, `KOLEGA_DB_NAME`, `KOLEGA_DB_USER`,
`KOLEGA_DB_PASS`. Lalu `php init.php` membuat tabel dan data contoh bila masih kosong.
Jalur MySQL belum diuji pada versi ini.

## Yang belum ada

Stok menu berkurang otomatis saat transaksi, tetapi stok bahan baku belum terhubung ke
menu. Belum ada laporan stok bahan baku per shift, kiriman laporan ke WhatsApp, dan
rekap QRIS otomatis dari DANA.
