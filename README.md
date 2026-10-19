# KolegaOps

Tampilan (frontend) sistem kasir **Warkop Kolega**, proyek PBL mata kuliah Desain dan Pemrograman Web.

**Tahap sekarang: hanya tampilan.** Belum ada database dan login. Datanya contoh (array PHP di
`includes/data.php`), jadi perubahan di halaman tidak tersimpan dan kembali seperti semula saat
halaman dimuat ulang. Database dan login dikerjakan di tahap berikutnya.

**Stack:** HTML5, CSS3, JavaScript dasar (DOM), PHP native.

## Halaman

| Halaman | Isi |
| --- | --- |
| **Dashboard** | Penjualan hari ini, laba kotor, stok menu menipis, bahan baku kritis, grafik 7 hari, transaksi terbaru |
| **Kasir** | Pilih menu (saring kategori, cari), atur jumlah, bayar tunai (kembalian otomatis) atau QRIS (total muncul), nama kasir dan shift tampil |
| **Struk** | Struk 80 mm siap cetak (tombol Cetak), tanpa watermark |
| **Manajemen Menu** | Tabel nama, kategori, **HPP**, harga jual, **profit**, stok, status aktif; cari dan saring; isi ulang stok (+50); hapus |
| **Tambah / Edit Produk** | Nama, kategori, HPP, harga jual dengan pratinjau profit langsung, peringatan bila harga di bawah HPP |
| **Manajemen Stok** | Bahan baku dengan status Kritis / Menipis / Aman, dialog Update (tambah, kurangi, set jumlah), tambah bahan |
| **Laporan Transaksi Harian** | Saring per shift dan kasir; ringkasan pendapatan, tunai, QRIS, HPP, laba, pengeluaran; hitungan uang laci per shift; riwayat transaksi dengan link struk |

Yang interaktif di browser (JavaScript): keranjang kasir, perhitungan bayar dan kembalian, stok menu
berkurang setelah bayar, saring tabel, status aktif/nonaktif, hapus baris, dialog stok, pratinjau profit,
dan perhitungan ulang laporan saat filter diganti. Semuanya hilang saat halaman dimuat ulang.

Halaman terbuka di layar HP (menu menjadi tombol hamburger).

## Menjalankan

Butuh PHP 8. Dari folder proyek ini:

```bash
php -S localhost:8000
```

Buka `http://localhost:8000`. Atau letakkan folder ini di `htdocs` / `www` Laragon.

## Struktur proyek

```
index.php             Dashboard
kasir/                index.php (kasir), struk.php (struk cetak)
menu/                 list.php, tambah.php, edit.php, _form.php (isian form bersama)
stok/                 list.php
laporan/              harian.php
includes/             header.php, footer.php, helpers.php, data.php (data contoh)
assets/               css/style.css, js/app.js
docs/                 kebutuhan-sistem.md
```

`includes/header.php` dan `footer.php` dipakai semua halaman (sidebar dan kerangka), sama seperti
pola Jobsheet 7. Alamat link dan asset dihitung otomatis dari kedalaman folder.

## Dokumentasi

- [Kebutuhan sistem](docs/kebutuhan-sistem.md): kebutuhan fungsional dan non-fungsional beserta statusnya.

## Tahap berikutnya

- Database PostgreSQL: mengganti `includes/data.php` dengan query, dan proses simpan/ubah/hapus
- Login per akun (1 pemilik, 5 pegawai) dan pembatasan hak akses
- Shift (buka/tutup, modal awal, pengeluaran), open bill, transaksi tersimpan
- Stok bahan baku terhubung ke menu, laporan stok per shift
- Rekap QRIS dan pengiriman laporan ke WhatsApp
