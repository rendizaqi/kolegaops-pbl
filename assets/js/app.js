// ===== Hamburger menu (tampilan layar kecil) =====
function initNavToggle() {
    const tombol = document.getElementById("nav-toggle-btn");
    const isi = document.getElementById("sidebar-isi");
    if (!tombol || !isi) return;

    tombol.addEventListener("click", function () {
        isi.classList.toggle("nav-open");
    });
}

// ===== Konfirmasi sebelum menghapus =====
// Form dengan class "form-hapus" meminta konfirmasi lewat confirm().
// Teks pertanyaan diambil dari atribut data-pesan.
function initKonfirmasiHapus() {
    document.querySelectorAll("form.form-hapus").forEach(function (form) {
        form.addEventListener("submit", function (event) {
            const pesan = form.dataset.pesan || "Yakin ingin menghapus data ini?";
            if (!confirm(pesan)) {
                event.preventDefault();
            }
        });
    });
}

// ===== Cetak struk =====
function initCetakStruk() {
    const tombol = document.getElementById("btn-cetak");
    if (!tombol) return;

    tombol.addEventListener("click", function () {
        window.print();
    });
}

document.addEventListener("DOMContentLoaded", function () {
    initNavToggle();
    initKonfirmasiHapus();
    initCetakStruk();
});
