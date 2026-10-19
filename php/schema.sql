-- KolegaOps — skema MySQL (dihasilkan dari includes/schema_seed.php).
-- Impor lewat phpMyAdmin, atau: mysql -u root -p kolegaops < schema.sql
-- Data contoh + akun demo diisi oleh init.php / setup.php.

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(60) NOT NULL,
    username VARCHAR(30) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role VARCHAR(10) NOT NULL DEFAULT 'pegawai',
    aktif INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    hpp INT NOT NULL DEFAULT 0,
    harga INT NOT NULL,
    stok INT NOT NULL DEFAULT 0,
    aktif INT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bahan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    minimum DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS shift (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(10) NOT NULL,
    kasir_id INT NOT NULL,
    kasir VARCHAR(60) NOT NULL,
    tanggal VARCHAR(10) NOT NULL,
    buka_at VARCHAR(19) NOT NULL,
    tutup_at VARCHAR(19) NULL,
    modal_awal INT NOT NULL DEFAULT 0,
    uang_fisik INT NULL,
    catatan VARCHAR(255) NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'buka'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL,
    shift_id INT NOT NULL,
    tanggal VARCHAR(10) NOT NULL,
    waktu VARCHAR(5) NOT NULL,
    kasir VARCHAR(60) NOT NULL,
    shift VARCHAR(10) NOT NULL,
    metode VARCHAR(10) NOT NULL DEFAULT 'tunai',
    total INT NOT NULL,
    hpp INT NOT NULL DEFAULT 0,
    bayar INT NOT NULL DEFAULT 0,
    kembalian INT NOT NULL DEFAULT 0,
    meja VARCHAR(30) NOT NULL DEFAULT '',
    status VARCHAR(10) NOT NULL DEFAULT 'lunas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaksi_item (
    id INT AUTO_INCREMENT PRIMARY KEY,
    transaksi_id INT NOT NULL,
    menu_id INT NOT NULL,
    nama VARCHAR(100) NOT NULL,
    qty INT NOT NULL,
    harga INT NOT NULL,
    hpp INT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pengeluaran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    shift_id INT NOT NULL,
    keterangan VARCHAR(100) NOT NULL,
    nominal INT NOT NULL,
    waktu VARCHAR(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ringkasan_harian (
    tanggal VARCHAR(10) PRIMARY KEY,
    hari VARCHAR(5) NOT NULL,
    total INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

