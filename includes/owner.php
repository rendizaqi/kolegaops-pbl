<?php
// Guard untuk halaman khusus pemilik (owner). Sudah termasuk cek login.
require __DIR__ . '/auth.php';

if (!$loginPemilik) {
    http_response_code(403);
    die('Halaman ini hanya untuk pemilik.');
}
