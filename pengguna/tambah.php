<?php
require __DIR__ . '/../includes/owner.php';
require __DIR__ . '/../includes/helpers.php';
require __DIR__ . '/../includes/csrf.php';
$page_title = "Tambah Akun";
include __DIR__ . '/../includes/header.php';
?>
        <div class="page-header">
            <div class="page-heading">
                <h1 class="page-title">Tambah Akun</h1>
                <span class="meta">Buat akun baru untuk pegawai atau pemilik.</span>
            </div>
        </div>

        <div class="content">
            <form id="form-akun" class="panel box form-grid" method="post" action="proses_tambah.php">
                <?php echo csrf_field(); ?>
                <p class="field">
                    <label class="label" for="nama">Nama</label>
                    <input class="input" type="text" id="nama" name="nama" required>
                </p>
                <p class="field">
                    <label class="label" for="username">Username</label>
                    <input class="input" type="text" id="username" name="username" pattern="[a-z0-9_.]{3,30}" title="3-30 karakter: huruf kecil, angka, titik, garis bawah" required>
                </p>
                <p class="field">
                    <label class="label" for="password">Kata sandi (min. 6 karakter)</label>
                    <input class="input" type="password" id="password" name="password" minlength="6" required>
                </p>
                <p class="field">
                    <label class="label" for="role">Peran</label>
                    <select class="input" id="role" name="role">
                        <option value="pegawai">Pegawai</option>
                        <option value="owner">Pemilik</option>
                    </select>
                </p>
                <p class="row-gap">
                    <button class="btn btn-primary btn-md" type="submit">Simpan</button>
                    <a class="btn btn-plain btn-md" href="list.php">Batal</a>
                </p>
            </form>
        </div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
