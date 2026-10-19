# Kebutuhan Sistem KolegaOps

Disusun dari wawancara kebutuhan Warkop Kolega dan wireframe awal. Versi sekarang **baru tampilan
(frontend)**: belum ada database dan login, jadi data hanya contoh dan perubahan tidak tersimpan.
Kolom **Status**:

- **Tampilan**: layar dan interaksinya sudah ada di browser, tetapi belum tersimpan ke database.
- **Ada**: berlaku untuk kebutuhan yang tidak bergantung pada database.
- **Rencana**: belum dikerjakan (butuh backend atau fitur lanjutan).
- **Perlu diuji**: belum diukur.

## Kebutuhan fungsional

Kebutuhan fungsional adalah *apa yang harus bisa dilakukan sistem*.

### Akun dan akses

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-01 | Setiap orang punya akun sendiri: 1 pemilik dan 5 pegawai | Rencana |
| F-02 | Pegawai login dan logout; saat ganti orang di kasir, akun diganti dan laporan tetap ada | Rencana |
| F-03 | Pemilik mengelola akun (tambah, ganti kata sandi, nonaktifkan) | Rencana |
| F-04 | Pembatasan hak akses: HPP, laba, laporan harian, dan pengelolaan menu hanya untuk pemilik | Rencana |

### Shift

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-05 | Perhitungan penjualan per shift (8 jam) | Tampilan |
| F-06 | Setiap shift punya satu kasir, bebas memakai akun siapa pun | Tampilan |
| F-07 | Shift ditutup paling lambat pukul 00:00 (peringatan bila lewat) agar laporan tidak rancu | Rencana |
| F-08 | Modal awal shift dapat memakai uang akhir shift sebelumnya | Rencana |
| F-09 | Catatan pengeluaran per shift (mis. es batu, bahan habis) | Rencana |
| F-10 | Penutupan shift menghitung selisih uang laci (lebih atau kurang) | Tampilan (di laporan) |

### Transaksi di kasir

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-11 | Memilih menu, mengatur jumlah, dan menghitung total | Tampilan |
| F-12 | Pembayaran tunai dengan kembalian otomatis | Tampilan |
| F-13 | Pembayaran QRIS: total yang harus dibayar langsung muncul | Tampilan |
| F-14 | Open bill: simpan pesanan, lanjutkan, atau hapus | Rencana |
| F-15 | Struk yang bisa dicetak, memuat produk, total, bayar, dan kembalian, tanpa watermark | Tampilan |
| F-16 | Riwayat transaksi dengan filter dan ekspor | Tampilan |
| F-17 | Nama kasir tercantum di kasir dan struk | Tampilan |

### Menu dan stok

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-18 | Tambah produk dengan nama, kategori, HPP, dan harga jual; profit terhitung | Tampilan |
| F-19 | Menu bisa diganti atau dinonaktifkan (direncanakan tiap 3 bulan) | Tampilan |
| F-20 | Setiap menu punya stok siap jual yang berkurang otomatis saat transaksi | Tampilan |
| F-21 | Peringatan saat stok menu tinggal 10 atau kurang | Tampilan |
| F-22 | Isi ulang stok lewat menu (biasanya 50), bukan lewat stok bahan baku | Tampilan |
| F-23 | Kelola stok bahan baku dengan penanda Kritis, Menipis, Aman | Tampilan |
| F-24 | Takaran perkiraan: sekian bahan baku menjadi sekian menu | Rencana |
| F-25 | Stok bahan baku dicek per minggu | Tampilan |
| F-26 | Sisa stok dilaporkan tiap shift, sehingga bisa dibedakan per shift | Rencana |

### Laporan

| Kode | Kebutuhan | Status |
| --- | --- | --- |
| F-27 | Laporan per shift: total tunai, total QRIS, pengeluaran, uang fisik, selisih | Tampilan |
| F-28 | Laporan harian yang sekaligus memuat HPP dan laba | Tampilan |
| F-29 | Laporan stok bahan baku | Rencana |
| F-30 | Pencocokan QRIS dengan laporan DANA dan laporan pemilik | Rencana |

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
| N-03 | Keamanan | Login wajib, kata sandi di-hash, perlindungan CSRF, XSS, dan SQL injection | Rencana |
| N-04 | Keamanan | Hak akses berdasarkan peran (pemilik atau pegawai) | Rencana |
| N-05 | Integritas data | Stok tidak boleh minus dan transaksi tersimpan utuh (database transaction) | Rencana |
| N-06 | Akurasi | Total dihitung di server dari harga database; QRIS dibayar pas agar tidak ada selisih kecil | Rencana |
| N-07 | Ketersediaan data | Laporan dan riwayat tetap ada setelah ganti akun atau shift | Rencana |
| N-08 | Kegunaan | Mudah dipakai pegawai: satu layar kasir, tombol jelas, bahasa Indonesia | Ada |
| N-09 | Tampilan | Responsif di laptop, tablet, dan HP | Ada |
| N-10 | Pemantauan | Pemilik dapat memantau dari aplikasi (selain dari chat grup) | Rencana |
| N-11 | Biaya dan batasan | Tidak dibatasi kuota atau waktu pemakaian dan tidak ada watermark di struk | Ada |
| N-12 | Kinerja | Halaman kasir cepat dibuka dan dipakai saat ramai | Perlu diuji |
| N-13 | Pemeliharaan | Struktur kode mengikuti materi kuliah (halaman, proses, includes) agar mudah dipahami | Ada |

## Perlu dikonfirmasi

- **Parkir:** pada catatan kebutuhan tertulis "yang parkir, harganya berapa". Belum jelas apakah ini
  pendapatan parkir yang dicatat di aplikasi, atau harga menu tertentu. Belum dikerjakan sampai ada kejelasan.
- **Jam shift:** belum ditentukan jam mulai dan selesai untuk Pagi, Siang, dan Malam.
- **Modal awal shift:** apakah memakai uang akhir shift sebelumnya (hari ini atau kemarin) perlu
  dikonfirmasi sebelum aturan ini dibangun di backend.
