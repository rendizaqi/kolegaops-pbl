# KolegaOps

Sistem kasir (POS) berbasis web untuk **Warkop Kolega**, dibuat sebagai proyek PBL mata kuliah
Desain dan Pemrograman Web.

**Stack:** HTML5, CSS3, JavaScript dasar (DOM), PHP native, PostgreSQL (PDO_PGSQL).

## Fitur

| Modul | Fungsi |
| --- | --- |
| **Login** | Akun sendiri untuk tiap orang (1 pemilik, 5 pegawai), kata sandi di-hash |
| **Shift** | Buka/tutup shift (8 jam, satu kasir), modal awal, pengeluaran, hitung uang laci dan selisihnya |
| **Kasir** | Pesanan, bayar tunai atau QRIS, kembalian otomatis, open bill, stok menu berkurang otomatis |
| **Struk** | Struk 80 mm siap cetak, tanpa watermark |
| **Riwayat transaksi** | Filter tanggal, shift, metode, status; ekspor CSV |
| **Manajemen menu** | Nama, kategori, HPP, harga jual, profit, stok menu, isi ulang stok (+50) |
| **Stok bahan baku** | Status Kritis / Menipis / Aman, update stok |
| **Laporan** | Per shift (tunai, QRIS, pengeluaran, selisih laci) dan harian (dengan HPP dan laba) |
| **Dashboard & akun** | Ringkasan pemilik, kelola akun pegawai |

### Hak akses

| Fitur | Pegawai | Pemilik |
| --- | --- | --- |
| Kasir, open bill, struk (hanya kasir shift yang sedang berjalan) | Ya | Ya |
| Buka/tutup shift, catat pengeluaran | Ya | Ya |
| Riwayat transaksi, laporan per shift | Ya (tanpa HPP dan laba) | Ya |
| Isi ulang stok menu, update stok bahan baku | Ya | Ya |
| Tambah/ubah/hapus menu dan harga, tambah bahan baku | Tidak | Ya |
| Dashboard, laporan harian, HPP dan laba, kelola akun | Tidak | Ya |

## ERD

```
users               menu                bahan
-----               ----                -----
id (PK)             id (PK)             id (PK)
nama                nama                nama
username (UQ)       kategori            qty
password (hash)     hpp                 satuan
role                harga               minimum
aktif               stok
                    aktif

shift                      transaksi                    transaksi_item
-----                      ---------                    --------------
id (PK)                    id (PK)                      id (PK)
nama (Pagi/Siang/Malam)    kode                         transaksi_id (FK -> transaksi.id)
kasir_id (FK -> users)     shift_id (FK -> shift.id)    menu_id
tanggal                    dibuat                       nama
buka_at, tutup_at          metode (tunai/qris)          qty
modal_awal                 total, hpp                   harga
uang_fisik                 bayar, kembalian             hpp
catatan                    meja
status (buka/tutup)        status (lunas/open/batal)    pengeluaran
                                                        -----------
ringkasan_harian                                        id (PK)
----------------                                        shift_id (FK -> shift.id)
tanggal (PK)                                            keterangan
total                                                   nominal, dibuat
```

## Instalasi dan menjalankan

1. **Buat database dan impor skema** (urutan penting karena ada foreign key):
   ```bash
   createdb kolegaops
   psql -d kolegaops -f sql/01_menu_bahan.sql
   psql -d kolegaops -f sql/02_users.sql
   psql -d kolegaops -f sql/03_shift_transaksi.sql
   psql -d kolegaops -f sql/04_data_contoh.sql
   ```
   File `04_data_contoh.sql` berisi akun demo, menu, bahan baku, dan dua shift hari ini.
2. **Atur koneksi** di `includes/config.php`. Nilai bawaannya `localhost:5432`, database `kolegaops`,
   user `postgres`, kata sandi `postgres`. Untuk server lain, isi lewat environment variable:
   ```bash
   DB_HOST=127.0.0.1 DB_NAME=kolegaops DB_USER=postgres DB_PASS=rahasia php -S localhost:8000
   ```
3. **Jalankan** dari folder proyek ini:
   ```bash
   php -S localhost:8000
   ```
   Atau lewat Laragon (Apache) dengan virtual host ke folder ini.
4. Buka `http://localhost:8000`.

### Akun demo

Semua akun memakai kata sandi **kolega123**. Ganti sebelum dipakai sungguhan.

| Akun | Username | Peran |
| --- | --- | --- |
| Pak Dimas | `dimas` | Pemilik |
| Rina, Andi, Bayu, Citra, Dewi | `rina`, `andi`, `bayu`, `citra`, `dewi` | Pegawai |

### Alur demo yang disarankan

1. Masuk sebagai **Bayu**, buka menu **Shift**, pilih *Malam*, isi modal awal, klik **Buka shift**.
2. Di **Kasir**, klik menu lalu bayar **Tunai** (kembalian otomatis) dan klik **Proses Bayar**. Buka struknya.
3. Buat pesanan lain: pilih **QRIS** (total muncul) atau **Simpan open bill** lalu **Lanjutkan**.
4. Di **Shift**, catat pengeluaran (mis. es batu), lalu **Tutup shift** dengan mengisi uang fisik di laci.
   Muncul laporan shift lengkap dengan selisih uang laci.
5. **Keluar**, masuk sebagai **Pak Dimas**: lihat Dashboard, **Laporan > Harian** (HPP dan laba),
   **Manajemen Menu** (HPP, harga jual, profit, isi ulang stok), dan **Akun**.

## Struktur proyek

```
index.php            Dashboard pemilik (pegawai diarahkan ke kasir)
auth/                login, proses_login, logout
kasir/               index, proses_keranjang, proses_bayar, proses_bill
shift/               index, proses_buka, proses_pengeluaran, proses_tutup
transaksi/           riwayat, struk
menu/                list, tambah, proses_tambah, edit, proses_edit, hapus, proses_status, proses_stok
bahan/               list, tambah, proses_tambah, update, proses_update
laporan/             shift (daftar dan detail), harian
pengguna/            list, tambah, proses_tambah, proses_password, proses_status
includes/            config, koneksi, helpers, csrf, auth, owner, shift, header, footer
assets/              css/style.css, js/app.js
sql/                 skema dan data contoh
docs/                kebutuhan sistem
```

## Keamanan

- Semua query memakai *prepared statement* (PDO), tanpa menyambung teks input ke SQL.
- Semua output dibungkus `e()` (`htmlspecialchars`) untuk mencegah XSS.
- Semua form yang mengubah data memakai token CSRF dan metode POST (hapus dan logout tidak lewat link).
- Kata sandi disimpan dengan `password_hash()` dan dicek dengan `password_verify()`.
- ID session diganti setelah login (`session_regenerate_id`).
- Halaman dijaga `includes/auth.php` (wajib login) dan `includes/owner.php` (khusus pemilik).
- Total pembayaran dihitung ulang di server dari harga database, bukan dari data browser.
- Penyimpanan transaksi memakai database transaction dengan `FOR UPDATE` agar stok tidak minus.

## Dokumentasi

- [Kebutuhan sistem](docs/kebutuhan-sistem.md): kebutuhan fungsional dan non-fungsional.

## Belum ada

Rencana pengembangan berikutnya:

- Stok bahan baku yang terhubung ke menu (takaran per menu) dan laporan sisa bahan per shift
- Rekap QRIS otomatis dari DANA (saat ini dicocokkan manual dengan laporan DANA)
- Pengiriman laporan ke WhatsApp
