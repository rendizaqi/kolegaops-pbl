-- Tabel menu (produk yang dijual) dan bahan baku
-- Jalankan: psql -d kolegaops -f sql/01_menu_bahan.sql

CREATE TABLE IF NOT EXISTS menu (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    hpp INTEGER NOT NULL DEFAULT 0,       -- harga pokok penjualan (modal per porsi)
    harga INTEGER NOT NULL,               -- harga jual
    stok INTEGER NOT NULL DEFAULT 0,      -- stok siap jual
    aktif SMALLINT NOT NULL DEFAULT 1     -- 1 = tampil di kasir, 0 = disembunyikan
);

CREATE TABLE IF NOT EXISTS bahan (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    qty NUMERIC(10,2) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    minimum NUMERIC(10,2) NOT NULL
);
