# Kebutuhan Sistem KolegaOps

Disusun dari wawancara kebutuhan Warkop Kolega dan prototipe awal. Kolom **Status** menunjukkan
kondisi di versi sekarang: **Ada** (sudah jalan), **Rencana** (belum dikerjakan), atau **Konfirmasi**
(maksud kebutuhan belum jelas).

## Kebutuhan fungsional

Kebutuhan fungsional adalah *apa yang harus bisa dilakukan sistem*.

### Akun dan akses

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-01 | Setiap orang punya akun sendiri: 1 pemilik dan 5 pegawai | Ada |
| F-02 | Pegawai login dan logout; saat ganti orang di kasir, akun diganti dan laporan tetap ada | Ada |
| F-03 | Pemilik mengelola akun (tambah, ganti kata sandi, nonaktifkan) | Ada |
| F-04 | Pembatasan hak akses: HPP, laba, laporan harian, dan pengelolaan menu hanya untuk pemilik | Ada |

### Shift

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-05 | Perhitungan penjualan per shift (8 jam) | Ada |
| F-06 | Setiap shift punya satu kasir, bebas memakai akun siapa pun | Ada |
| F-07 | Shift ditutup paling lambat pukul 00:00 (peringatan bila lewat) agar laporan tidak rancu | Ada (peringatan) |
| F-08 | Modal awal shift dapat memakai uang akhir shift sebelumnya | Ada |
| F-09 | Catatan pengeluaran per shift (mis. es batu, bahan habis) | Ada |
| F-10 | Penutupan shift menghitung selisih uang laci (lebih atau kurang) | Ada |

### Transaksi di kasir

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-11 | Memilih menu, mengatur jumlah, dan menghitung total | Ada |
| F-12 | Pembayaran tunai dengan kembalian otomatis | Ada |
| F-13 | Pembayaran QRIS: total yang harus dibayar langsung muncul | Ada |
| F-14 | Open bill: simpan pesanan, lanjutkan, atau hapus | Ada |
| F-15 | Struk yang bisa dicetak, memuat produk, total, bayar, dan kembalian, tanpa watermark | Ada |
| F-16 | Riwayat transaksi dengan filter dan ekspor | Ada |
| F-17 | Nama kasir tercantum di kasir dan struk | Ada |

### Menu dan stok

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-18 | Tambah produk dengan nama, kategori, HPP, dan harga jual; profit terhitung | Ada |
| F-19 | Menu bisa diganti atau dinonaktifkan (direncanakan tiap 3 bulan) | Ada |
| F-20 | Setiap menu punya stok siap jual yang berkurang otomatis saat transaksi | Ada |
| F-21 | Peringatan saat stok menu tinggal 10 atau kurang | Ada |
| F-22 | Isi ulang stok lewat menu (biasanya 50), bukan lewat stok bahan baku | Ada |
| F-23 | Kelola stok bahan baku dengan penanda Kritis, Menipis, Aman | Ada |
| F-24 | Takaran perkiraan: sekian bahan baku menjadi sekian menu | Rencana |
| F-25 | Stok bahan baku dicek per minggu | Ada (tampilan) |
| F-26 | Sisa stok dilaporkan tiap shift, sehingga bisa dibedakan per shift | Rencana |

### Laporan

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-27 | Laporan per shift: total tunai, total QRIS, pengeluaran, uang fisik, selisih | Ada |
| F-28 | Laporan harian yang sekaligus memuat HPP dan laba | Ada |
| F-29 | Laporan stok bahan baku | Rencana |
| F-30 | Pencocokan QRIS dengan laporan DANA dan laporan pemilik | Rencana (manual) |

### Integrasi

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-31 | Laporan stok dan harian terkirim otomatis ke WhatsApp (saat ini dilaporkan manual di grup) | Rencana |

## Kebutuhan non-fungsional

Kebutuhan non-fungsional adalah *kualitas dan batasan* sistem: seberapa baik, seberapa aman, dan dalam
kondisi apa sistem bekerja.

| Kode | Kategori | Kebutuhan | Status |
| --- | --- | --- | --- |
| N-01 | Platform | Berupa aplikasi web yang dibuka lewat browser | Ada |
| N-02 | Teknologi | PHP native dan PostgreSQL, tanpa framework | Ada |
| N-03 | Keamanan | Login wajib, kata sandi di-hash, perlindungan CSRF, XSS, dan SQL injection | Ada |
| N-04 | Keamanan | Hak akses berdasarkan peran (pemilik atau pegawai) | Ada |
| N-05 | Integritas data | Stok tidak boleh minus dan transaksi tersimpan utuh (database transaction) | Ada |
| N-06 | Akurasi | Total dihitung di server dari harga database; QRIS dibayar pas agar tidak ada selisih kecil | Ada |
| N-07 | Ketersediaan data | Laporan dan riwayat tetap ada setelah ganti akun atau shift | Ada |
| N-08 | Kegunaan | Mudah dipakai pegawai: satu layar kasir, tombol jelas, bahasa Indonesia | Ada |
| N-09 | Tampilan | Responsif di laptop, tablet, dan HP | Ada |
| N-10 | Pemantauan | Pemilik dapat memantau dari aplikasi (selain dari chat grup) | Ada |
| N-11 | Biaya dan batasan | Tidak dibatasi kuota atau waktu pemakaian dan tidak ada watermark di struk | Ada |
| N-12 | Kinerja | Halaman kasir cepat dibuka dan dipakai saat ramai | Perlu diuji |
| N-13 | Pemeliharaan | Struktur kode mengikuti materi kuliah (halaman, proses, includes) agar mudah dipahami | Ada |

## Perlu dikonfirmasi

- **Parkir:** pada catatan kebutuhan tertulis "yang parkir, harganya berapa". Belum jelas apakah ini
  pendapatan parkir yang dicatat di aplikasi, atau harga menu tertentu. Belum dikerjakan sampai ada kejelasan.
- **Jam shift:** belum ditentukan jam mulai dan selesai untuk Pagi, Siang, dan Malam. Saat ini shift
  dibuka dan ditutup manual oleh kasir.
- **Penggunaan uang shift:** apakah modal awal memakai uang shift hari ini atau kemarin sudah dibantu
  lewat saran modal dari uang akhir shift terakhir, tetapi aturan pastinya perlu dikonfirmasi.
