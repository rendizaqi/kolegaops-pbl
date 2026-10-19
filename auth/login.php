<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
require __DIR__ . '/../includes/koneksi.php';

$flash = $_SESSION['flash'] ?? null;
unset($_SESSION['flash']);

$daftarAkun = $pdo->query("SELECT nama, username, role FROM users WHERE aktif = 1 ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk | KolegaOps</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;600;800&display=swap">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body">
    <main class="login">
        <div class="login-brand">
            <div class="brand-logo">Kolega<span>Ops</span></div>
            <div class="brand-sub">Warkop Kolega</div>
        </div>

        <form class="login-card" method="post" action="proses_login.php">
            <?php echo csrf_field(); ?>
            <h1 class="login-title">Masuk</h1>
            <p class="muted">Pilih akun sesuai orang yang bertugas di kasir.</p>

            <?php if ($flash): ?>
                <p class="alert alert-err"><?php echo e($flash['pesan']); ?></p>
            <?php endif; ?>

            <p class="field">
                <label class="label" for="username">Akun</label>
                <select class="input input-strong" id="username" name="username" required>
                    <?php foreach ($daftarAkun as $akun): ?>
                    <option value="<?php echo e($akun['username']); ?>">
                        <?php echo e($akun['nama']); ?> (<?php echo $akun['role'] === 'owner' ? 'Pemilik' : 'Pegawai'; ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p class="field">
                <label class="label" for="password">Kata sandi</label>
                <input class="input input-strong" type="password" id="password" name="password" required autofocus>
            </p>
            <button class="btn btn-primary login-btn" type="submit"><span>Masuk</span><span>&rarr;</span></button>
            <p class="demo-hint">Demo: semua akun memakai kata sandi <strong>kolega123</strong></p>
        </form>
    </main>
</body>
</html>
