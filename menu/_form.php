<?php
// Isian form produk, dipakai tambah.php dan edit.php.
// Variabel dari halaman pemanggil: $isi (nilai awal), $kategori (daftar kategori).
?>
                <p class="field">
                    <label class="label" for="nama">Nama produk</label>
                    <input class="input" type="text" id="nama" name="nama" value="<?php echo e($isi['nama']); ?>" required>
                </p>
                <p class="field">
                    <label class="label" for="kategori">Kategori</label>
                    <select class="input" id="kategori" name="kategori">
                        <?php foreach ($kategori as $k): ?>
                        <option<?php echo $isi['kategori'] === $k ? ' selected' : ''; ?>><?php echo e($k); ?></option>
                        <?php endforeach; ?>
                    </select>
                </p>
                <div class="row-gap">
                    <p class="field grow">
                        <label class="label" for="hpp">HPP (Rp)</label>
                        <input class="input" type="number" id="hpp" name="hpp" min="0" value="<?php echo e($isi['hpp']); ?>" placeholder="Modal per porsi" required>
                    </p>
                    <p class="field grow">
                        <label class="label" for="harga">Harga jual (Rp)</label>
                        <input class="input" type="number" id="harga" name="harga" min="1" value="<?php echo e($isi['harga']); ?>" required>
                    </p>
                </div>
                <div class="profit-box" id="profit-box">
                    <span class="label">Profit per porsi</span>
                    <span class="profit-nilai" id="profit-nilai">Rp 0</span>
                    <span class="meta" id="profit-persen">Isi HPP dan harga jual.</span>
                </div>
                <p class="field">
                    <label class="label" for="stok">Stok siap jual</label>
                    <input class="input" type="number" id="stok" name="stok" min="0" value="<?php echo e($isi['stok']); ?>" required>
                </p>
                <p class="check">
                    <input type="checkbox" id="aktif" name="aktif" value="1"<?php echo $isi['aktif'] ? ' checked' : ''; ?>>
                    <label for="aktif">Aktif (tampil di kasir)</label>
                </p>
                <p class="pesan-form alert alert-err hidden" id="pesan-form"></p>
                <p class="row-gap">
                    <button class="btn btn-primary btn-md" type="submit">Simpan</button>
                    <a class="btn btn-plain btn-md" href="list.php">Batal</a>
                </p>
