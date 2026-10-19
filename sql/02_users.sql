-- Tabel users: 1 pemilik (owner) + pegawai, masing-masing punya akun sendiri
-- Jalankan: psql -d kolegaops -f sql/02_users.sql

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,                  -- hasil password_hash()
    role VARCHAR(20) NOT NULL DEFAULT 'pegawai',     -- 'owner' atau 'pegawai'
    aktif SMALLINT NOT NULL DEFAULT 1
);
