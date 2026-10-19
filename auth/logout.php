<?php
session_start();
require __DIR__ . '/../includes/csrf.php';

// Logout lewat POST + token CSRF supaya tidak bisa dipicu dari link luar.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();
    $_SESSION = [];
    session_destroy();
}

header('Location: login.php');
exit;
