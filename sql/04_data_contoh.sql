-- Data contoh: akun demo (kata sandi: kolega123), menu, bahan baku, dua shift hari ini.
-- Jalankan SETELAH 01, 02, 03:  psql -d kolegaops -f sql/04_data_contoh.sql
-- Tanggal memakai CURRENT_DATE, jadi data selalu terlihat 'hari ini'.

INSERT INTO users (id, nama, username, password, role, aktif) VALUES
    (1, 'Pak Dimas', 'dimas', '$2y$12$W5Qy44wqsijinFPWER4q9egrG7sXBWc7gDHvhsgYxCwihYryThHGu', 'owner', 1),
    (2, 'Rina', 'rina', '$2y$12$W5Qy44wqsijinFPWER4q9egrG7sXBWc7gDHvhsgYxCwihYryThHGu', 'pegawai', 1),
    (3, 'Andi', 'andi', '$2y$12$W5Qy44wqsijinFPWER4q9egrG7sXBWc7gDHvhsgYxCwihYryThHGu', 'pegawai', 1),
    (4, 'Bayu', 'bayu', '$2y$12$W5Qy44wqsijinFPWER4q9egrG7sXBWc7gDHvhsgYxCwihYryThHGu', 'pegawai', 1),
    (5, 'Citra', 'citra', '$2y$12$W5Qy44wqsijinFPWER4q9egrG7sXBWc7gDHvhsgYxCwihYryThHGu', 'pegawai', 1),
    (6, 'Dewi', 'dewi', '$2y$12$W5Qy44wqsijinFPWER4q9egrG7sXBWc7gDHvhsgYxCwihYryThHGu', 'pegawai', 1);

INSERT INTO menu (nama, kategori, hpp, harga, stok, aktif) VALUES
    ('Kopi Hitam', 'Kopi', 1800, 5000, 40, 1),
    ('Kopi Susu Kolega', 'Kopi', 3500, 10000, 35, 1),
    ('Es Kopi Gula Aren', 'Kopi', 5200, 15000, 8, 1),
    ('Kopi Tubruk', 'Kopi', 2000, 6000, 30, 1),
    ('Teh Tarik', 'Non-Kopi', 3000, 10000, 25, 1),
    ('Es Teh Manis', 'Non-Kopi', 1200, 5000, 50, 1),
    ('Es Jeruk', 'Non-Kopi', 2800, 8000, 0, 0),
    ('Indomie Goreng', 'Makanan', 5500, 12000, 6, 1),
    ('Indomie Rebus Telur', 'Makanan', 6800, 14000, 20, 1),
    ('Nasi Goreng Kolega', 'Makanan', 8000, 18000, 15, 1),
    ('Roti Bakar Coklat', 'Snack', 5000, 13000, 12, 1),
    ('Pisang Goreng', 'Snack', 3500, 10000, 10, 1);

INSERT INTO bahan (nama, qty, satuan, minimum) VALUES
    ('Biji kopi robusta', 1.2, 'kg', 2),
    ('Biji kopi arabika', 3.5, 'kg', 1.5),
    ('Susu kental manis', 14, 'kaleng', 10),
    ('Gula pasir', 8, 'kg', 3),
    ('Gula aren cair', 0.8, 'liter', 1),
    ('Teh celup', 120, 'pcs', 50),
    ('Indomie goreng', 18, 'bungkus', 24),
    ('Telur ayam', 30, 'butir', 20),
    ('Roti tawar', 5, 'bungkus', 4),
    ('Es batu', 6, 'kg', 10);

INSERT INTO shift (id, nama, kasir_id, tanggal, buka_at, tutup_at, modal_awal, uang_fisik, catatan, status) VALUES
    (1, 'Pagi', 2, CURRENT_DATE, CURRENT_DATE + TIME '06:00', CURRENT_DATE + TIME '14:00', 200000, 253000, 'Uang laci kurang, dicek ulang besok.', 'tutup'),
    (2, 'Siang', 3, CURRENT_DATE, CURRENT_DATE + TIME '14:00', CURRENT_DATE + TIME '22:00', 250000, 322000, NULL, 'tutup');

INSERT INTO transaksi (id, kode, shift_id, dibuat, metode, total, hpp, bayar, kembalian, meja, status) VALUES
    (1, 'TRX-0001', 1, CURRENT_DATE + TIME '06:12', 'tunai', 15000, 4800, 20000, 5000, 'Meja 2', 'lunas'),
    (2, 'TRX-0002', 1, CURRENT_DATE + TIME '06:40', 'qris', 10000, 3500, 10000, 0, 'Meja 3', 'lunas'),
    (3, 'TRX-0003', 1, CURRENT_DATE + TIME '07:05', 'tunai', 33000, 12200, 40000, 7000, 'Meja 4', 'lunas'),
    (4, 'TRX-0004', 1, CURRENT_DATE + TIME '08:18', 'tunai', 6000, 2000, 10000, 4000, 'Meja 5', 'batal'),
    (5, 'TRX-0005', 1, CURRENT_DATE + TIME '09:30', 'qris', 25000, 8700, 25000, 0, 'Meja 6', 'lunas'),
    (6, 'TRX-0006', 1, CURRENT_DATE + TIME '11:20', 'tunai', 22000, 8500, 30000, 8000, 'Meja 1', 'lunas'),
    (7, 'TRX-0007', 2, CURRENT_DATE + TIME '14:22', 'tunai', 34000, 13800, 40000, 6000, 'Meja 2', 'lunas'),
    (8, 'TRX-0008', 2, CURRENT_DATE + TIME '15:10', 'qris', 36000, 16000, 36000, 0, 'Meja 3', 'lunas'),
    (9, 'TRX-0009', 2, CURRENT_DATE + TIME '16:45', 'tunai', 28000, 10200, 30000, 2000, 'Meja 4', 'lunas'),
    (10, 'TRX-0010', 2, CURRENT_DATE + TIME '18:30', 'qris', 34000, 13400, 34000, 0, 'Meja 5', 'lunas'),
    (11, 'TRX-0011', 2, CURRENT_DATE + TIME '20:05', 'tunai', 30000, 10500, 30000, 0, 'Meja 6', 'lunas');

INSERT INTO transaksi_item (transaksi_id, menu_id, nama, qty, harga, hpp) VALUES
    (1, 1, 'Kopi Hitam', 2, 5000, 1800),
    (1, 6, 'Es Teh Manis', 1, 5000, 1200),
    (2, 2, 'Kopi Susu Kolega', 1, 10000, 3500),
    (3, 10, 'Nasi Goreng Kolega', 1, 18000, 8000),
    (3, 1, 'Kopi Hitam', 1, 5000, 1800),
    (3, 6, 'Es Teh Manis', 2, 5000, 1200),
    (4, 4, 'Kopi Tubruk', 1, 6000, 2000),
    (5, 3, 'Es Kopi Gula Aren', 1, 15000, 5200),
    (5, 12, 'Pisang Goreng', 1, 10000, 3500),
    (6, 8, 'Indomie Goreng', 1, 12000, 5500),
    (6, 5, 'Teh Tarik', 1, 10000, 3000),
    (7, 9, 'Indomie Rebus Telur', 1, 14000, 6800),
    (7, 2, 'Kopi Susu Kolega', 2, 10000, 3500),
    (8, 10, 'Nasi Goreng Kolega', 2, 18000, 8000),
    (9, 11, 'Roti Bakar Coklat', 1, 13000, 5000),
    (9, 3, 'Es Kopi Gula Aren', 1, 15000, 5200),
    (10, 8, 'Indomie Goreng', 2, 12000, 5500),
    (10, 6, 'Es Teh Manis', 2, 5000, 1200),
    (11, 2, 'Kopi Susu Kolega', 1, 10000, 3500),
    (11, 12, 'Pisang Goreng', 2, 10000, 3500);

INSERT INTO pengeluaran (shift_id, keterangan, nominal, dibuat) VALUES
    (1, 'Es batu', 15000, CURRENT_DATE + TIME '07:30'),
    (2, 'Gas LPG', 20000, CURRENT_DATE + TIME '16:00');

-- Penjualan 6 hari sebelumnya untuk grafik dashboard (hari ini dihitung dari transaksi)
INSERT INTO ringkasan_harian (tanggal, total) VALUES
    (CURRENT_DATE - 6, 2310000),
    (CURRENT_DATE - 5, 2580000),
    (CURRENT_DATE - 4, 3420000),
    (CURRENT_DATE - 3, 3890000),
    (CURRENT_DATE - 2, 3150000),
    (CURRENT_DATE - 1, 2540000);

-- Samakan urutan SERIAL dengan data yang sudah diisi manual
SELECT setval(pg_get_serial_sequence('users', 'id'), (SELECT MAX(id) FROM users));
SELECT setval(pg_get_serial_sequence('menu', 'id'), (SELECT MAX(id) FROM menu));
SELECT setval(pg_get_serial_sequence('bahan', 'id'), (SELECT MAX(id) FROM bahan));
SELECT setval(pg_get_serial_sequence('shift', 'id'), (SELECT MAX(id) FROM shift));
SELECT setval(pg_get_serial_sequence('transaksi', 'id'), (SELECT MAX(id) FROM transaksi));
SELECT setval(pg_get_serial_sequence('transaksi_item', 'id'), (SELECT MAX(id) FROM transaksi_item));
SELECT setval(pg_get_serial_sequence('pengeluaran', 'id'), (SELECT MAX(id) FROM pengeluaran));
