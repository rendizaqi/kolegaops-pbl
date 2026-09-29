-- KolegaOps — skema MySQL. Impor lewat phpMyAdmin, atau:
--   mysql -u root -p kolegaops < schema.sql
-- Untuk mengisi data contoh, jalankan setup.php sesudahnya (php setup.php).
-- (setup.php juga bisa membuat tabel ini sendiri; file ini untuk yang lebih suka impor SQL.)

CREATE TABLE IF NOT EXISTS menu (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    kategori VARCHAR(30) NOT NULL,
    harga INT NOT NULL,
    aktif TINYINT NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS bahan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    qty DECIMAL(10,2) NOT NULL,
    satuan VARCHAR(20) NOT NULL,
    minimum DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS transaksi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL,
    waktu VARCHAR(5) NOT NULL,
    tanggal VARCHAR(10) NOT NULL,
    kasir VARCHAR(50) NOT NULL,
    shift VARCHAR(10) NOT NULL,
    total INT NOT NULL,
    status VARCHAR(10) NOT NULL DEFAULT 'lunas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS ringkasan_harian (
    tanggal VARCHAR(10) PRIMARY KEY,
    hari VARCHAR(5) NOT NULL,
    total INT NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
