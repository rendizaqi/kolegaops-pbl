<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/koneksi.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
$page_title = "Akun";

$daftarUser = $pdo->query("SELECT * FROM users ORDER BY id")->fetchAll();

include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Akun</h1>
                <span class="meta">Setiap orang punya akun sendiri &middot; 1 pemilik, sisanya pegawai</span>
            </div>
            <a class="btn btn-primary" href="tambah.php"><span>+</span><span>Tambah Akun</span></a>
        </div>

        <div class="content">
            <section class="panel">
                <div class="table-responsive">
                <table class="tabel">
                    <thead><tr><th>Nama</th><th>Username</th><th>Peran</th><th>Status</th><th>Aksi</th></tr></thead>
                    <tbody>
                        <?php foreach ($daftarUser as $u): ?>
                        <tr>
                            <td class="name"><?php echo e($u['nama']); ?></td>
                            <td class="mono"><?php echo e($u['username']); ?></td>
                            <td><?php echo $u['role'] === 'owner' ? '<span class="badge badge-ok">Pemilik</span>' : 'Pegawai'; ?></td>
                            <td><?php echo (int) $u['aktif'] ? '<span class="badge badge-ok">Aktif</span>' : '<span class="badge badge-bad">Nonaktif</span>'; ?></td>
                            <td>
                                <div class="row-actions">
                                    <form method="post" action="proses_password.php" class="restock">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                                        <input class="input input-sm input-lebar" type="password" name="password" placeholder="Sandi baru" minlength="6" aria-label="Kata sandi baru <?php echo e($u['nama']); ?>">
                                        <button class="btn btn-outline btn-sm" type="submit">Ganti sandi</button>
                                    </form>
                                    <?php if ((int) $u['id'] !== (int) $_SESSION['user_id']): ?>
                                    <form method="post" action="proses_status.php">
                                        <?php echo csrf_field(); ?>
                                        <input type="hidden" name="id" value="<?php echo (int) $u['id']; ?>">
                                        <button class="btn btn-danger btn-sm" type="submit"><?php echo (int) $u['aktif'] ? 'Nonaktifkan' : 'Aktifkan'; ?></button>
                                    </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                </div>
            </section>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
