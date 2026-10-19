<?php
// Pemisahan kredensial dari kode sumber (includes/koneksi.php).
// Di server, isi nilai-nilai ini lewat environment variable (getenv)
// dan JANGAN meng-commit kredensial asli ke repository publik.
return [
    'db_host' => getenv('DB_HOST') ?: 'localhost',
    'db_port' => getenv('DB_PORT') ?: '5432',
    'db_name' => getenv('DB_NAME') ?: 'kolegaops',
    'db_user' => getenv('DB_USER') ?: 'postgres',
    'db_pass' => getenv('DB_PASS') ?: 'postgres',
];
