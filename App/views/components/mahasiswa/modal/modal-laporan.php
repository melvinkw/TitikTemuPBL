<?php
/**
 * App/views/components/mahasiswa/modal/modal-laporan.php
 * Modal form "Laporan Kehilangan / Penemuan Barang".
 *
 * Cara pakai di halaman:
 *   $jenisLaporan = 'hilang';   // pilihan: hilang atau temuan
 *   $linkTutup    = base_url('mahasiswa/dashboard.php');   // tujuan tombol Batal
 *   require_once APP_ROOT . '/App/views/components/mahasiswa/modal/modal-laporan.php';
 *
 * Kalau jenisnya "temuan", ada tambahan kolom "Titik penyerahan barang temuan".
 * Tombol belum berfungsi (belum ada proses ke database).
 *
 * Dropdown: <select> bawaan browser disembunyikan panahnya (appearance-none),
 * lalu diganti ikon chevron dari Figma (icon-chevron-down.svg).
 */
$jenisLaporan = $jenisLaporan ?? 'hilang';
$linkTutup    = $linkTutup ?? '#';
?>
<!-- Latar gelap di belakang modal -->
<div class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto bg-black/40 p-4">

    <!-- Kotak modal -->
    <form action="#" method="POST" class="my-8 w-full max-w-[806px] rounded-2xl bg-white p-[18px]">

        <!-- Judul -->
        <div class="p-4">
            <h2 class="font-display text-xl font-semibold">Laporan Kehilangan / Penemuan Barang</h2>
            <p class="mt-3 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- Jenis laporan (dropdown) -->
        <label class="mt-4 block font-display text-base font-semibold">Jenis Laporan *</label>
        <div class="relative mt-2">
            <select name="jenis" class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white px-3 pr-12 text-sm">
                <option value="hilang" <?= $jenisLaporan === 'hilang' ? 'selected' : '' ?>>Barang Hilang</option>
                <option value="temuan" <?= $jenisLaporan === 'temuan' ? 'selected' : '' ?>>Barang Temuan</option>
            </select>
            <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3 h-6 w-6">
        </div>

        <!-- Kategori (dropdown) -->
        <label class="mt-4 block font-display text-base font-semibold">Kategori barang*</label>
        <div class="relative mt-2">
            <select name="kategori" class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white px-3 pr-12 text-sm">
                <option value="">Pilih Kategori</option>
                <option value="elektronik">Elektronik</option>
                <option value="aksesoris">Aksesoris</option>
                <option value="identitas">Identitas</option>
            </select>
            <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3 h-6 w-6">
        </div>

        <!-- Nama barang & email -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-display text-base font-semibold">Nama Barang*</label>
                <input type="text" name="nama_barang" placeholder="Contoh: Earbuds, Tumbler, Charger Laptop"
                       class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>
            <div>
                <label class="block font-display text-base font-semibold">Email*</label>
                <input type="email" name="email"
                       class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>
        </div>

        <!-- Deskripsi -->
        <label class="mt-4 block font-display text-base font-semibold">Deskripsi dan ciri khusus*</label>
        <textarea name="deskripsi" rows="3" placeholder="Tuliskan detail/ciri-ciri umum barang"
                  class="mt-2 h-[89px] w-full rounded-[10px] border border-tt-outline p-3 text-sm placeholder:text-tt-outline"></textarea>

        <!-- Tanggal (dengan ikon kalender) & lokasi (dropdown) -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-display text-base font-semibold">Tanggal dan waktu kejadian*</label>
                <div class="relative mt-2">
                    <!-- Ikon kalender bawaan browser dibuat transparan, posisinya tetap di kanan
                         sehingga saat ikon Figma di atasnya diklik, pemilih tanggal tetap terbuka -->
                    <input type="datetime-local" name="waktu"
                           class="h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm [&::-webkit-calendar-picker-indicator]:opacity-0">
                    <img src="<?= base_url('assets/images/icon-kalender.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3.5 h-[21px] w-[22px]">
                </div>
            </div>
            <div>
                <label class="block font-display text-base font-semibold">Lokasi Kejadian*</label>
                <div class="relative mt-2">
                    <select name="lokasi" class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white px-3 pr-12 text-sm">
                        <option value="">Pilih Lokasi</option>
                        <option value="lpr-8">LPR 8</option>
                        <option value="gedung-sipil">Lobby Gedung Sipil lt 1</option>
                        <option value="gedung-ti">Gedung Teknologi Informasi</option>
                    </select>
                    <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3 h-6 w-6">
                </div>
            </div>
        </div>

        <!-- Foto (+ titik penyerahan khusus barang temuan) -->
        <div class="mt-4 grid grid-cols-1 gap-4 <?= $jenisLaporan === 'temuan' ? 'md:grid-cols-2' : '' ?>">
            <div>
                <label class="block font-display text-base font-semibold">
                    <?= $jenisLaporan === 'temuan' ? 'Foto barang (max 3)*' : 'Foto barang (opsional)' ?>
                </label>
                <!-- Kotak pilih file: input asli disembunyikan, kotaknya bisa diklik -->
                <label class="mt-2 flex h-12 w-full cursor-pointer items-center gap-2.5 rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="h-6 w-6">
                    <span>No File Choosen</span>
                    <input type="file" name="foto[]" multiple class="hidden"
                           onchange="this.previousElementSibling.textContent = this.files.length + ' file dipilih'">
                </label>
                <p class="mt-2 text-xs">JPG, PNG, WebP, PDF maksimal 5 MB.</p>
            </div>

            <?php if ($jenisLaporan === 'temuan'): ?>
                <div>
                    <label class="block font-display text-base font-semibold">Titik penyerahan barang temuan*</label>
                    <div class="relative mt-2">
                        <select name="pos" class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white px-3 pr-12 text-sm">
                            <option value="pos-1">Pos 1</option>
                            <option value="pos-2">Pos 2</option>
                        </select>
                        <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3 h-6 w-6">
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tombol -->
        <div class="mt-4 flex gap-2.5 p-2.5">
            <button type="button" class="h-[43px] rounded-[10px] bg-tt-navy px-[11px] text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">Kirim Laporan</button>
            <a href="<?= $linkTutup ?>" class="flex h-[43px] items-center rounded-[10px] border border-tt-navy px-[11px] text-sm font-semibold hover:bg-indigo-100">Batal</a>
        </div>
    </form>
</div>
