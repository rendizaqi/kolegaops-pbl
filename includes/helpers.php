<?php
// Membungkus output data yang berasal dari input pengguna sebelum
// dicetak ke HTML, untuk mencegah XSS (Cross-Site Scripting).
function e($value)
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

// Format rupiah: 15000 -> "Rp 15.000"
function rp($angka)
{
    return 'Rp ' . number_format((float) round((float) $angka), 0, ',', '.');
}

// Angka desimal gaya Indonesia tanpa nol berlebih: 1.5 -> "1,5", 120 -> "120"
function angka_id($angka)
{
    $teks = number_format((float) $angka, 2, ',', '.');
    return rtrim(rtrim($teks, '0'), ',');
}

// Ambil angka bulat dari input bebas, mis. "100.000" -> 100000
function ke_angka($teks)
{
    return (int) preg_replace('/\D/', '', (string) $teks);
}

// "Selasa, 29 Sep 2026" dari 'Y-m-d' (kosong = hari ini)
function tanggal_id($ymd = null)
{
    $waktu = $ymd ? strtotime($ymd) : time();
    $hari = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
    $bulan = ['', 'Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    return $hari[(int) date('w', $waktu)] . ', ' . date('j', $waktu) . ' ' . $bulan[(int) date('n', $waktu)] . ' ' . date('Y', $waktu);
}

// "09:15" dari timestamp database
function jam($timestamp)
{
    return $timestamp ? date('H:i', strtotime($timestamp)) : '';
}

function hari_singkat($ymd)
{
    $nama = ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'];
    return $nama[(int) date('w', strtotime($ymd))];
}

// Pesan satu kali pakai yang ditampilkan oleh header.php setelah redirect.
// $tipe: 'success', 'error', atau 'warning'
function flash($tipe, $pesan)
{
    $_SESSION['flash'] = ['type' => $tipe, 'pesan' => $pesan];
}

// Stok menu dianggap tinggal sedikit bila <= batas ini.
const STOK_MENU_RENDAH = 10;
// Jumlah isi ulang stok menu yang biasa dipakai.
const STOK_MENU_ISI_ULANG = 50;

function daftar_shift()
{
    return ['Pagi', 'Siang', 'Malam'];
}

function daftar_kategori()
{
    return ['Kopi', 'Non-Kopi', 'Makanan', 'Snack'];
}

// Status stok bahan baku: kritis (<= minimum), menipis (<= 1,5x minimum), selebihnya aman.
function status_bahan($qty, $minimum)
{
    if ($qty <= $minimum) {
        return 'kritis';
    }
    return $qty <= $minimum * 1.5 ? 'menipis' : 'aman';
}
