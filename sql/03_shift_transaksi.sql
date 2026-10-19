-- Shift, transaksi, item transaksi, pengeluaran, dan ringkasan harian
-- Jalankan: psql -d kolegaops -f sql/03_shift_transaksi.sql

CREATE TABLE IF NOT EXISTS shift (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(10) NOT NULL,                       -- Pagi / Siang / Malam
    kasir_id INTEGER NOT NULL REFERENCES users(id),  -- satu kasir per shift
    tanggal DATE NOT NULL DEFAULT CURRENT_DATE,
    buka_at TIMESTAMP NOT NULL DEFAULT NOW(),
    tutup_at TIMESTAMP,
    modal_awal INTEGER NOT NULL DEFAULT 0,           -- uang tunai di laci saat shift dibuka
    uang_fisik INTEGER,                              -- uang tunai di laci saat shift ditutup
    catatan VARCHAR(255),
    status VARCHAR(10) NOT NULL DEFAULT 'buka'       -- 'buka' atau 'tutup'
);

CREATE TABLE IF NOT EXISTS transaksi (
    id SERIAL PRIMARY KEY,
    kode VARCHAR(20) NOT NULL DEFAULT '',            -- TRX-0001 (lunas) / BILL-0001 (open bill)
    shift_id INTEGER NOT NULL REFERENCES shift(id),
    dibuat TIMESTAMP NOT NULL DEFAULT NOW(),
    metode VARCHAR(10) NOT NULL DEFAULT 'tunai',     -- 'tunai' atau 'qris'
    total INTEGER NOT NULL,
    hpp INTEGER NOT NULL DEFAULT 0,                  -- total HPP (untuk laba)
    bayar INTEGER NOT NULL DEFAULT 0,
    kembalian INTEGER NOT NULL DEFAULT 0,
    meja VARCHAR(30) NOT NULL DEFAULT '',
    status VARCHAR(10) NOT NULL DEFAULT 'lunas'      -- 'lunas', 'open', atau 'batal'
);

CREATE TABLE IF NOT EXISTS transaksi_item (
    id SERIAL PRIMARY KEY,
    transaksi_id INTEGER NOT NULL REFERENCES transaksi(id) ON DELETE CASCADE,
    menu_id INTEGER NOT NULL,
    nama VARCHAR(100) NOT NULL,                      -- disalin agar struk lama tidak berubah
    qty INTEGER NOT NULL,
    harga INTEGER NOT NULL,
    hpp INTEGER NOT NULL DEFAULT 0
);

CREATE TABLE IF NOT EXISTS pengeluaran (
    id SERIAL PRIMARY KEY,
    shift_id INTEGER NOT NULL REFERENCES shift(id),
    keterangan VARCHAR(100) NOT NULL,
    nominal INTEGER NOT NULL,
    dibuat TIMESTAMP NOT NULL DEFAULT NOW()
);

CREATE TABLE IF NOT EXISTS ringkasan_harian (
    tanggal DATE PRIMARY KEY,
    total INTEGER NOT NULL
);
