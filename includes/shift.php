<?php
// Fungsi bantu shift & laporan. Butuh variabel $pdo (includes/koneksi.php).

// Shift yang sedang berjalan (hanya satu pada satu waktu), atau false.
function shift_aktif($pdo)
{
    $stmt = $pdo->query(
        "SELECT s.*, u.nama AS kasir
         FROM shift s JOIN users u ON u.id = s.kasir_id
         WHERE s.status = 'buka' ORDER BY s.id DESC LIMIT 1"
    );
    return $stmt->fetch();
}

// Hanya kasir shift yang sedang berjalan yang boleh mencatat transaksi.
// Dipakai di halaman proses_*.php; bila tidak berhak, kembali ke $kembali.
function wajib_kasir_shift($pdo, $kembali)
{
    $shift = shift_aktif($pdo);
    if (!$shift || (int) $shift['kasir_id'] !== (int) $_SESSION['user_id']) {
        flash('error', 'Hanya kasir shift yang sedang berjalan yang bisa mencatat transaksi.');
        header('Location: ' . $kembali);
        exit;
    }
    return $shift;
}

// Ringkasan satu shift: tunai, QRIS, total, HPP, laba kotor, pengeluaran.
function statistik_shift($pdo, $shiftId)
{
    $hasil = ['tunai' => 0, 'qris' => 0, 'total' => 0, 'jumlah' => 0, 'hpp' => 0, 'batal' => 0, 'pengeluaran' => 0];

    $stmt = $pdo->prepare(
        "SELECT metode, COUNT(*) AS jumlah, COALESCE(SUM(total), 0) AS total, COALESCE(SUM(hpp), 0) AS hpp
         FROM transaksi WHERE shift_id = :id AND status = 'lunas' GROUP BY metode"
    );
    $stmt->execute(['id' => $shiftId]);
    foreach ($stmt->fetchAll() as $baris) {
        $hasil[$baris['metode'] === 'qris' ? 'qris' : 'tunai'] += (int) $baris['total'];
        $hasil['total'] += (int) $baris['total'];
        $hasil['jumlah'] += (int) $baris['jumlah'];
        $hasil['hpp'] += (int) $baris['hpp'];
    }

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM transaksi WHERE shift_id = :id AND status = 'batal'");
    $stmt->execute(['id' => $shiftId]);
    $hasil['batal'] = (int) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(nominal), 0) FROM pengeluaran WHERE shift_id = :id");
    $stmt->execute(['id' => $shiftId]);
    $hasil['pengeluaran'] = (int) $stmt->fetchColumn();

    $hasil['laba'] = $hasil['total'] - $hasil['hpp'];
    return $hasil;
}

// Uang tunai yang seharusnya ada di laci: modal awal + penjualan tunai - pengeluaran.
// QRIS tidak dihitung karena langsung masuk ke HP pemilik.
function kas_diharapkan($shift, $statistik)
{
    return (int) $shift['modal_awal'] + $statistik['tunai'] - $statistik['pengeluaran'];
}

// Label selisih uang laci (HTML): Pas / Lebih / Kurang
function label_selisih($selisih)
{
    if ($selisih === 0) {
        return '<span class="selisih selisih-ok">Pas</span>';
    }
    return $selisih > 0
        ? '<span class="selisih selisih-lebih">Lebih ' . e(rp($selisih)) . '</span>'
        : '<span class="selisih selisih-kurang">Kurang ' . e(rp(-$selisih)) . '</span>';
}
