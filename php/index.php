<?php
// Halaman masuk: pemilik ke dashboard, pegawai ke kasir, belum login ke halaman masuk.
require __DIR__ . '/includes/functions.php';
$u = require_login();
redirect($u['role'] === 'owner' ? 'dashboard.php' : 'kasir.php');
