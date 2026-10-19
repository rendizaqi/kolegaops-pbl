<?php
/**
 * Konfigurasi koneksi database KolegaOps.
 *
 * Untuk deploy di hosting (cPanel/phpMyAdmin), pakai driver 'mysql' dan isi
 * nama database, user, dan password sesuai hosting Anda. Nilai bisa juga
 * di-set lewat environment variable (KOLEGA_DB_*) tanpa mengubah file ini.
 */
return [
    // 'sqlite' (bawaan, tanpa server DB, cocok untuk demo) atau 'mysql' untuk produksi.
    'driver'      => getenv('KOLEGA_DB_DRIVER') ?: 'sqlite',

    // Pengaturan MySQL:
    'host'        => getenv('KOLEGA_DB_HOST') ?: '127.0.0.1',
    'port'        => getenv('KOLEGA_DB_PORT') ?: '3306',
    'name'        => getenv('KOLEGA_DB_NAME') ?: 'kolegaops',
    'user'        => getenv('KOLEGA_DB_USER') ?: 'root',
    'pass'        => getenv('KOLEGA_DB_PASS') ?: '',

    // Lokasi file bila driver = 'sqlite':
    'sqlite_path' => getenv('KOLEGA_DB_SQLITE') ?: __DIR__ . '/data/kolegaops.sqlite',
];
