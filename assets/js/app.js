// KolegaOps — JavaScript sisi browser (DOM & event biasa, tanpa library).
// Semua perubahan hanya terjadi di halaman; belum ada penyimpanan ke database.

// ===== Fungsi bantu =====

// 15000 -> "Rp 15.000"
function formatRp(angka) {
    return "Rp " + Math.round(angka).toLocaleString("id-ID");
}

// "100.000" -> 100000 (ambil digit saja)
function ambilAngka(teks) {
    return parseInt(String(teks).replace(/\D/g, ""), 10) || 0;
}

// "1,5" atau "1.5" -> 1.5
function ambilDesimal(teks) {
    return parseFloat(String(teks).replace(",", ".")) || 0;
}

// 1.5 -> "1,5", 120 -> "120"
function formatDesimal(angka) {
    return String(Math.round(angka * 100) / 100).replace(".", ",");
}

function buatElemen(tag, kelas, teks) {
    const el = document.createElement(tag);
    if (kelas) el.className = kelas;
    if (teks !== undefined) el.textContent = teks;
    return el;
}

// Tampilkan pesan sebentar lalu sembunyikan lagi.
function tampilkanPesan(el, teks) {
    el.textContent = teks;
    el.classList.remove("hidden");
    clearTimeout(el.timer);
    el.timer = setTimeout(function () {
        el.classList.add("hidden");
    }, 4000);
}

// ===== Menu hamburger (layar kecil) =====
function initNavToggle() {
    const tombol = document.getElementById("nav-toggle-btn");
    const isi = document.getElementById("sidebar-isi");
    if (!tombol || !isi) return;

    tombol.addEventListener("click", function () {
        isi.classList.toggle("nav-open");
    });
}

// ===== Struk: cetak dan tutup =====
function initStruk() {
    const cetak = document.getElementById("btn-cetak");
    const tutup = document.getElementById("btn-tutup");
    if (cetak) cetak.addEventListener("click", function () { window.print(); });
    if (tutup) tutup.addEventListener("click", function () { window.close(); });
}

// ===== Kasir =====
function initKasir() {
    const halaman = document.getElementById("halaman-kasir");
    if (!halaman) return;

    const kartu = Array.from(document.querySelectorAll(".menu-card"));
    const chips = document.querySelectorAll(".kasir-cats .chip");
    const kolomCari = document.getElementById("cari-menu");
    const menuKosong = document.getElementById("menu-kosong");

    const daftarPesanan = document.getElementById("daftar-pesanan");
    const pesananKosong = document.getElementById("pesanan-kosong");
    const pesanSukses = document.getElementById("pesan-sukses");
    const pesanError = document.getElementById("pesan-error");
    const inputBayar = document.getElementById("input-bayar");
    const btnBayar = document.getElementById("btn-bayar");
    const btnKosongkan = document.getElementById("btn-kosongkan");

    let pesanan = {};          // id menu -> { id, nama, harga, qty }
    let metode = "tunai";
    let bayar = 0;
    let kategoriAktif = "Semua";

    function stokAwal(k) {
        return parseInt(k.dataset.stok, 10);
    }

    function qtyDipesan(id) {
        return pesanan[id] ? pesanan[id].qty : 0;
    }

    // Label stok di kartu = stok dikurangi yang sudah masuk pesanan.
    function perbaruiKartu() {
        kartu.forEach(function (k) {
            const sisa = stokAwal(k) - qtyDipesan(k.dataset.id);
            const tag = k.querySelector(".stok-tag");
            tag.className = "stok-tag";
            if (sisa <= 0) {
                tag.textContent = "Stok habis";
                tag.classList.add("stok-habis");
            } else if (sisa <= 10) {
                tag.textContent = "Sisa " + sisa;
                tag.classList.add("stok-rendah");
            } else {
                tag.textContent = "Stok " + sisa;
            }
            k.disabled = sisa <= 0;
        });
    }

    function hitungTotal() {
        let total = 0;
        let jumlah = 0;
        Object.values(pesanan).forEach(function (p) {
            total += p.harga * p.qty;
            jumlah += p.qty;
        });
        return { total: total, jumlah: jumlah };
    }

    function buatBarisPesanan(p) {
        const baris = buatElemen("div", "order-line");

        const info = buatElemen("div", "order-line-info");
        info.appendChild(buatElemen("span", "name", p.nama));
        info.appendChild(buatElemen("span", "meta", formatRp(p.harga) + " / item"));
        baris.appendChild(info);

        const stepper = buatElemen("div", "stepper");
        const kurang = buatElemen("button", null, "−");
        kurang.type = "button";
        kurang.dataset.aksi = "kurang";
        kurang.dataset.id = p.id;
        kurang.setAttribute("aria-label", "Kurangi " + p.nama);
        const tambah = buatElemen("button", null, "+");
        tambah.type = "button";
        tambah.dataset.aksi = "tambah";
        tambah.dataset.id = p.id;
        tambah.setAttribute("aria-label", "Tambah " + p.nama);
        stepper.appendChild(kurang);
        stepper.appendChild(buatElemen("span", null, String(p.qty)));
        stepper.appendChild(tambah);
        baris.appendChild(stepper);

        baris.appendChild(buatElemen("span", "order-line-sub", formatRp(p.harga * p.qty)));

        const hapus = buatElemen("button", "order-line-remove", "×");
        hapus.type = "button";
        hapus.title = "Hapus item";
        hapus.dataset.aksi = "hapus";
        hapus.dataset.id = p.id;
        baris.appendChild(hapus);
        return baris;
    }

    function render() {
        daftarPesanan.innerHTML = "";
        Object.values(pesanan).forEach(function (p) {
            daftarPesanan.appendChild(buatBarisPesanan(p));
        });

        const hasil = hitungTotal();
        const adaItem = hasil.jumlah > 0;
        pesananKosong.classList.toggle("hidden", adaItem);

        document.getElementById("jumlah-item").textContent = hasil.jumlah;
        document.getElementById("subtotal").textContent = formatRp(hasil.total);
        document.getElementById("total").textContent = formatRp(hasil.total);
        document.getElementById("total-qris").textContent = formatRp(hasil.total);

        // Tunai: kembalian atau kekurangan. QRIS: dibayar pas, tanpa kembalian.
        const tertutup = metode === "qris" || bayar >= hasil.total;
        const kurang = metode === "tunai" && bayar > 0 && bayar < hasil.total;
        document.getElementById("label-kembalian").textContent = kurang ? "Kurang" : "Kembalian";
        document.getElementById("nilai-kembalian").textContent =
            formatRp(kurang ? hasil.total - bayar : (adaItem && tertutup ? bayar - hasil.total : 0));

        btnBayar.disabled = !(adaItem && tertutup);
        btnKosongkan.disabled = !adaItem;
        perbaruiKartu();
    }

    function tambahItem(id) {
        const k = kartu.find(function (x) { return x.dataset.id === id; });
        if (qtyDipesan(id) + 1 > stokAwal(k)) {
            tampilkanPesan(pesanError, "Stok " + k.dataset.nama + " tidak cukup.");
            return;
        }
        if (!pesanan[id]) {
            pesanan[id] = { id: id, nama: k.dataset.nama, harga: parseInt(k.dataset.harga, 10), qty: 0 };
        }
        pesanan[id].qty++;
        render();
    }

    function kurangiItem(id) {
        if (!pesanan[id]) return;
        pesanan[id].qty--;
        if (pesanan[id].qty <= 0) delete pesanan[id];
        render();
    }

    function kosongkan() {
        pesanan = {};
        bayar = 0;
        inputBayar.value = "";
        render();
    }

    // Saring kartu menu berdasarkan kategori dan kata kunci.
    function terapkanFilter() {
        const kata = kolomCari.value.trim().toLowerCase();
        let tampil = 0;
        kartu.forEach(function (k) {
            const cocok = (kategoriAktif === "Semua" || k.dataset.kategori === kategoriAktif)
                && k.dataset.nama.toLowerCase().indexOf(kata) !== -1;
            k.classList.toggle("hidden", !cocok);
            if (cocok) tampil++;
        });
        menuKosong.classList.toggle("hidden", tampil > 0);
    }

    kartu.forEach(function (k) {
        k.addEventListener("click", function () { tambahItem(k.dataset.id); });
    });

    daftarPesanan.addEventListener("click", function (event) {
        const tombol = event.target.closest("button[data-aksi]");
        if (!tombol) return;
        if (tombol.dataset.aksi === "tambah") tambahItem(tombol.dataset.id);
        if (tombol.dataset.aksi === "kurang") kurangiItem(tombol.dataset.id);
        if (tombol.dataset.aksi === "hapus") {
            delete pesanan[tombol.dataset.id];
            render();
        }
    });

    chips.forEach(function (chip) {
        chip.addEventListener("click", function () {
            chips.forEach(function (c) { c.classList.remove("is-active"); });
            chip.classList.add("is-active");
            kategoriAktif = chip.dataset.kategori;
            terapkanFilter();
        });
    });
    kolomCari.addEventListener("input", terapkanFilter);

    // Metode bayar: Tunai atau QRIS.
    document.querySelectorAll(".pay-methods .seg-opt").forEach(function (tombol) {
        tombol.addEventListener("click", function () {
            metode = tombol.dataset.metode;
            document.querySelectorAll(".pay-methods .seg-opt").forEach(function (t) { t.classList.remove("is-active"); });
            tombol.classList.add("is-active");
            document.getElementById("panel-tunai").classList.toggle("hidden", metode === "qris");
            document.getElementById("panel-qris").classList.toggle("hidden", metode === "tunai");
            render();
        });
    });

    // Nominal bayar: tampil dengan pemisah ribuan.
    inputBayar.addEventListener("input", function () {
        bayar = ambilAngka(inputBayar.value);
        inputBayar.value = bayar ? bayar.toLocaleString("id-ID") : "";
        render();
    });

    document.querySelectorAll(".quick-cash button").forEach(function (tombol) {
        tombol.addEventListener("click", function () {
            bayar = tombol.dataset.bayar === "pas" ? hitungTotal().total : parseInt(tombol.dataset.bayar, 10);
            inputBayar.value = bayar ? bayar.toLocaleString("id-ID") : "";
            render();
        });
    });

    btnBayar.addEventListener("click", function () {
        const hasil = hitungTotal();
        const dibayar = metode === "qris" ? hasil.total : bayar;
        if (hasil.total === 0 || dibayar < hasil.total) return;

        // Stok menu berkurang sesuai jumlah yang terjual.
        Object.values(pesanan).forEach(function (p) {
            const k = kartu.find(function (x) { return x.dataset.id === p.id; });
            k.dataset.stok = stokAwal(k) - p.qty;
        });

        let teks = "Pembayaran " + formatRp(hasil.total) + " (" + metode.toUpperCase() + ") berhasil";
        if (metode === "tunai") teks += " · kembalian " + formatRp(dibayar - hasil.total);
        tampilkanPesan(pesanSukses, teks + ".");

        kosongkan();
    });

    btnKosongkan.addEventListener("click", kosongkan);

    render();
}

// ===== Manajemen menu: daftar =====
function initMenuList() {
    const tabel = document.getElementById("tabel-menu");
    if (!tabel) return;

    const baris = Array.from(tabel.querySelectorAll("tbody tr[data-nama]"));
    const kolomCari = document.getElementById("cari");
    const pilihKategori = document.getElementById("filter-kategori");
    const pilihStatus = document.getElementById("filter-status");
    const barisKosong = document.getElementById("menu-tidak-ada");
    const ringkas = document.getElementById("ringkas-menu");

    function perbaruiRingkasan() {
        const ada = baris.filter(function (b) { return document.body.contains(b); });
        const aktif = ada.filter(function (b) { return b.dataset.aktif === "1"; });
        ringkas.textContent = ada.length + " produk · " + aktif.length + " aktif";
    }

    function terapkanFilter() {
        const kata = kolomCari.value.trim().toLowerCase();
        let tampil = 0;
        baris.forEach(function (b) {
            if (!document.body.contains(b)) return;
            const cocok = b.dataset.nama.indexOf(kata) !== -1
                && (pilihKategori.value === "" || b.dataset.kategori === pilihKategori.value)
                && (pilihStatus.value === "" || (pilihStatus.value === "aktif") === (b.dataset.aktif === "1"));
            b.classList.toggle("hidden", !cocok);
            if (cocok) tampil++;
        });
        barisKosong.classList.toggle("hidden", tampil > 0);
    }

    // Tampilan stok: angka biasa, "Sisa N" (kuning) bila <= 10, "Habis" (merah) bila 0.
    function tampilkanStok(b) {
        const sel = b.querySelector(".sel-stok");
        const stok = parseInt(b.dataset.stok, 10);
        sel.innerHTML = "";
        if (stok <= 0) {
            sel.appendChild(buatElemen("span", "badge badge-status badge-kritis", "Habis"));
        } else if (stok <= 10) {
            sel.appendChild(buatElemen("span", "badge badge-status badge-menipis", "● Sisa " + stok));
        } else {
            sel.appendChild(buatElemen("span", "strong", String(stok)));
        }
    }

    baris.forEach(function (b) {
        // Aktif / nonaktif
        b.querySelector(".switch").addEventListener("click", function () {
            const aktif = b.dataset.aktif !== "1";
            b.dataset.aktif = aktif ? "1" : "0";
            b.classList.toggle("row-off", !aktif);
            this.classList.toggle("is-on", aktif);
            this.querySelector(".switch-label").textContent = aktif ? "Aktif" : "Nonaktif";
            perbaruiRingkasan();
            terapkanFilter();
        });

        // Isi ulang stok (biasanya 50)
        b.querySelector(".btn-tambah-stok").addEventListener("click", function () {
            const jumlah = ambilAngka(b.querySelector(".input-jumlah").value);
            if (jumlah <= 0) return;
            b.dataset.stok = parseInt(b.dataset.stok, 10) + jumlah;
            tampilkanStok(b);
        });

        // Hapus baris (hanya di tampilan) dengan konfirmasi
        b.querySelector(".btn-hapus-baris").addEventListener("click", function () {
            if (confirm(this.dataset.pesan || "Hapus data ini?")) {
                b.remove();
                perbaruiRingkasan();
                terapkanFilter();
            }
        });
    });

    kolomCari.addEventListener("input", terapkanFilter);
    pilihKategori.addEventListener("change", terapkanFilter);
    pilihStatus.addEventListener("change", terapkanFilter);
}

// ===== Form produk (tambah / edit) =====
function initFormMenu() {
    const form = document.getElementById("form-menu");
    if (!form) return;

    const hpp = document.getElementById("hpp");
    const harga = document.getElementById("harga");
    const kotak = document.getElementById("profit-box");
    const nilai = document.getElementById("profit-nilai");
    const persen = document.getElementById("profit-persen");
    const pesan = document.getElementById("pesan-form");

    // Profit = harga jual - HPP, dihitung langsung saat mengetik.
    function hitungProfit() {
        const h = parseInt(hpp.value, 10) || 0;
        const j = parseInt(harga.value, 10) || 0;
        const profit = j - h;
        nilai.textContent = formatRp(profit);
        kotak.classList.toggle("rugi", j > 0 && profit < 0);
        persen.textContent = j > 0
            ? "Margin " + Math.round(profit / j * 100) + "% dari harga jual"
            : "Isi HPP dan harga jual.";
    }

    hpp.addEventListener("input", hitungProfit);
    harga.addEventListener("input", hitungProfit);
    hitungProfit();

    // Validasi di browser (di server nanti diperiksa lagi).
    form.addEventListener("submit", function (event) {
        const h = parseInt(hpp.value, 10) || 0;
        const j = parseInt(harga.value, 10) || 0;
        let kesalahan = "";
        if (document.getElementById("nama").value.trim() === "") {
            kesalahan = "Nama produk wajib diisi.";
        } else if (j <= 0) {
            kesalahan = "Harga jual harus lebih dari 0.";
        } else if (j < h) {
            kesalahan = "Harga jual lebih kecil dari HPP, produk akan rugi.";
        }
        if (kesalahan) {
            event.preventDefault();
            pesan.textContent = kesalahan;
            pesan.classList.remove("hidden");
        }
    });
}

// ===== Manajemen stok bahan baku =====
function initStok() {
    const tabel = document.getElementById("tabel-stok");
    if (!tabel) return;

    const tbody = tabel.querySelector("tbody");
    const dialogUpdate = document.getElementById("dialog-update");
    const dialogBahan = document.getElementById("dialog-bahan");
    let barisAktif = null;
    let mode = "tambah";

    // Kritis bila <= minimum, menipis bila <= 1,5x minimum, selebihnya aman.
    function statusBahan(qty, min) {
        if (qty <= min) return "kritis";
        return qty <= min * 1.5 ? "menipis" : "aman";
    }

    function gambarBaris(b) {
        const qty = parseFloat(b.dataset.qty);
        const min = parseFloat(b.dataset.min);
        const st = statusBahan(qty, min);
        const warna = { kritis: "var(--danger)", menipis: "var(--warn-line)", aman: "var(--blue)" }[st];
        const persen = Math.min(100, qty / Math.max(0.01, min * 3) * 100);

        const selQty = b.querySelector(".sel-qty");
        selQty.innerHTML = "";
        const level = buatElemen("span", "stok-level");
        level.appendChild(buatElemen("span", "strong stok-qty", formatDesimal(qty)));
        const bar = buatElemen("span", "stok-bar");
        const isi = document.createElement("span");
        isi.style.width = Math.round(persen) + "%";
        isi.style.background = warna;
        bar.appendChild(isi);
        level.appendChild(bar);
        selQty.appendChild(level);

        const selStatus = b.querySelector(".sel-status");
        selStatus.innerHTML = "";
        if (st === "kritis") selStatus.appendChild(buatElemen("span", "badge badge-status badge-kritis", "● Kritis"));
        if (st === "menipis") selStatus.appendChild(buatElemen("span", "badge badge-status badge-menipis", "● Menipis"));
        if (st === "aman") selStatus.appendChild(buatElemen("span", "badge badge-status badge-aman", "Aman"));
    }

    function hitungRingkasan() {
        const semua = Array.from(tbody.querySelectorAll("tr"));
        const hitung = { kritis: 0, menipis: 0, aman: 0 };
        semua.forEach(function (b) {
            hitung[statusBahan(parseFloat(b.dataset.qty), parseFloat(b.dataset.min))]++;
        });
        document.getElementById("jml-total").textContent = semua.length;
        document.getElementById("jml-menipis").textContent = hitung.menipis;
        document.getElementById("jml-kritis").textContent = hitung.kritis;
    }

    function buka(dialog) {
        dialog.classList.remove("hidden");
        const kolom = dialog.querySelector("input");
        if (kolom) kolom.focus();
    }

    function tutup() {
        dialogUpdate.classList.add("hidden");
        dialogBahan.classList.add("hidden");
    }

    function pilihMode(nama) {
        mode = nama;
        document.querySelectorAll("#mode-picker .chip").forEach(function (c) {
            c.classList.toggle("is-active", c.dataset.mode === nama);
        });
    }

    // Tombol Update di setiap baris (termasuk baris yang ditambah belakangan).
    tbody.addEventListener("click", function (event) {
        const tombol = event.target.closest(".btn-update-stok");
        if (!tombol) return;
        barisAktif = tombol.closest("tr");
        document.getElementById("info-update").textContent =
            barisAktif.dataset.nama + " · stok saat ini " + formatDesimal(parseFloat(barisAktif.dataset.qty)) + " " + barisAktif.dataset.satuan;
        document.getElementById("label-jumlah").textContent = "Jumlah (" + barisAktif.dataset.satuan + ")";
        document.getElementById("jumlah-update").value = "";
        document.getElementById("catatan-update").value = "";
        pilihMode("tambah");
        buka(dialogUpdate);
    });

    document.querySelectorAll("#mode-picker .chip").forEach(function (c) {
        c.addEventListener("click", function () { pilihMode(c.dataset.mode); });
    });

    document.getElementById("form-update").addEventListener("submit", function (event) {
        event.preventDefault();
        const jumlah = ambilDesimal(document.getElementById("jumlah-update").value);
        let qty = parseFloat(barisAktif.dataset.qty);
        if (mode === "tambah") qty += jumlah;
        if (mode === "kurangi") qty -= jumlah;
        if (mode === "set") qty = jumlah;
        barisAktif.dataset.qty = Math.max(0, Math.round(qty * 100) / 100);
        gambarBaris(barisAktif);
        hitungRingkasan();
        tutup();
    });

    // Tambah bahan baru ke tabel.
    document.getElementById("btn-tambah-bahan").addEventListener("click", function () {
        document.getElementById("form-bahan").reset();
        buka(dialogBahan);
    });

    document.getElementById("form-bahan").addEventListener("submit", function (event) {
        event.preventDefault();
        const nama = document.getElementById("bahan-nama").value.trim();
        const satuan = document.getElementById("bahan-satuan").value.trim();
        if (nama === "" || satuan === "") return;
        const qty = ambilDesimal(document.getElementById("bahan-qty").value);
        const min = ambilDesimal(document.getElementById("bahan-min").value);

        const b = document.createElement("tr");
        b.dataset.nama = nama;
        b.dataset.qty = qty;
        b.dataset.min = min;
        b.dataset.satuan = satuan;
        b.appendChild(buatElemen("td", "name", nama));
        b.appendChild(buatElemen("td", "sel-qty"));
        b.appendChild(buatElemen("td", "muted", satuan));
        b.appendChild(buatElemen("td", "muted", formatDesimal(min)));
        b.appendChild(buatElemen("td", "sel-status"));
        const aksi = document.createElement("td");
        const tombol = buatElemen("button", "btn btn-outline btn-sm btn-update-stok", "Update");
        tombol.type = "button";
        aksi.appendChild(tombol);
        b.appendChild(aksi);
        tbody.appendChild(b);

        gambarBaris(b);
        hitungRingkasan();
        tutup();
    });

    document.querySelectorAll(".btn-tutup-dialog").forEach(function (t) {
        t.addEventListener("click", tutup);
    });
    document.querySelectorAll(".dialog-backdrop").forEach(function (latar) {
        latar.addEventListener("click", function (event) {
            if (event.target === latar) tutup();
        });
    });
    document.addEventListener("keydown", function (event) {
        if (event.key === "Escape") tutup();
    });

    hitungRingkasan();
}

// ===== Laporan transaksi harian =====
function initLaporan() {
    const tabel = document.getElementById("tabel-transaksi");
    if (!tabel) return;

    const barisTransaksi = Array.from(tabel.querySelectorAll("tbody tr[data-shift]"));
    const barisShift = Array.from(document.querySelectorAll("#tabel-shift tbody tr"));
    const kosong = document.getElementById("transaksi-kosong");
    const pilihKasir = document.getElementById("filter-kasir");
    let shiftAktif = "Semua";

    function cocok(el) {
        return (shiftAktif === "Semua" || el.dataset.shift === shiftAktif)
            && (pilihKasir.value === "" || el.dataset.kasir === pilihKasir.value);
    }

    // Saring tabel lalu hitung ulang kartu ringkasan dari baris yang tampil.
    function terapkan() {
        let jumlah = 0, lunas = 0, batal = 0, pendapatan = 0, tunai = 0, qris = 0, hpp = 0, keluar = 0;

        barisTransaksi.forEach(function (b) {
            const tampil = cocok(b);
            b.classList.toggle("hidden", !tampil);
            if (!tampil) return;
            jumlah++;
            if (b.dataset.status === "batal") {
                batal++;
                return;
            }
            const total = parseInt(b.dataset.total, 10);
            lunas++;
            pendapatan += total;
            hpp += parseInt(b.dataset.hpp, 10);
            if (b.dataset.metode === "qris") qris += total; else tunai += total;
        });

        barisShift.forEach(function (b) {
            const tampil = cocok(b);
            b.classList.toggle("hidden", !tampil);
            if (tampil) keluar += parseInt(b.dataset.keluar, 10);
        });

        kosong.classList.toggle("hidden", jumlah > 0);
        document.getElementById("r-pendapatan").textContent = formatRp(pendapatan);
        document.getElementById("r-lunas").textContent = lunas + " transaksi lunas";
        document.getElementById("r-jumlah").textContent = jumlah;
        document.getElementById("r-batal").textContent = batal + " dibatalkan";
        document.getElementById("r-tunai").textContent = formatRp(tunai);
        document.getElementById("r-qris").textContent = formatRp(qris);
        document.getElementById("r-hpp").textContent = formatRp(hpp);
        document.getElementById("r-laba").textContent = formatRp(pendapatan - hpp);
        document.getElementById("r-rata").textContent = formatRp(lunas > 0 ? pendapatan / lunas : 0);
        document.getElementById("r-keluar").textContent = formatRp(keluar);
    }

    document.querySelectorAll("#seg-shift .seg-opt").forEach(function (tombol) {
        tombol.addEventListener("click", function () {
            document.querySelectorAll("#seg-shift .seg-opt").forEach(function (t) { t.classList.remove("is-active"); });
            tombol.classList.add("is-active");
            shiftAktif = tombol.dataset.shift;
            terapkan();
        });
    });
    pilihKasir.addEventListener("change", terapkan);

    terapkan();
}

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initStruk();
    initKasir();
    initMenuList();
    initFormMenu();
    initStok();
    initLaporan();
});
