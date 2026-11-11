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
    <form
        action="<?= h(base_url('mahasiswa/laporan/simpan.php')) ?>"
        method="POST"
        enctype="multipart/form-data"
        class="my-8 w-full max-w-[806px] rounded-2xl bg-white p-[18px]">
        <?= csrf_field() ?>
        <!-- Judul -->
        <div class="p-4">
            <h2 class="font-display text-xl font-semibold">Laporan Kehilangan / Penemuan Barang</h2>
            <p class="mt-3 text-xs">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- Jenis laporan (dropdown) -->
        <label class="mt-4 block font-display text-base font-semibold">Jenis Laporan *</label>
        <div class="relative mt-2">
            <select
                name="jenis"
                id="jenis-laporan"
                required
                class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white px-3 pr-12 text-sm">
                <option value="hilang" <?= $jenisLaporan === 'hilang' ? 'selected' : '' ?>>Barang Hilang</option>
                <option value="temuan" <?= $jenisLaporan === 'temuan' ? 'selected' : '' ?>>Barang Temuan</option>
            </select>
            <img src="<?= base_url('assets/images/icon-chevron-down.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3 h-6 w-6">
        </div>

        <!-- Kategori (dropdown) -->
        <!-- Kategori -->
        <label class="mt-4 block font-display text-base font-semibold">
            Kategori barang*
        </label>

        <div class="relative mt-2">
            <select
                name="id_kategori"
                required
                class="h-12 w-full appearance-none rounded-[10px] border border-tt-outline bg-white px-3 pr-12 text-sm">
                <option value="">Pilih Kategori</option>

                <?php foreach ($kategoriAktif as $kategori): ?>
                    <option value="<?= (int) $kategori['id_kategori'] ?>">
                        <?= h($kategori['nama_kategori']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <img
                src="<?= h(base_url('assets/images/icon-chevron-down.svg')) ?>"
                alt=""
                class="pointer-events-none absolute right-3 top-3 h-6 w-6">
        </div>

        <!-- Nama barang & email -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-display text-base font-semibold">Nama Barang*</label>
                <input
                    type="text"
                    name="judul"
                    maxlength="150"
                    required
                    placeholder="Contoh: Earbuds, Tumbler, Charger Laptop"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>
            <div>
                <label class="block font-display text-base font-semibold">Email*</label>
                <input
                    type="email"
                    name="email_kontak"
                    maxlength="254"
                    required
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div>
        </div>

        <!-- Deskripsi -->
        <label class="mt-4 block font-display text-base font-semibold">Deskripsi dan ciri khusus*</label>
        <textarea
            name="deskripsi"
            rows="3"
            required
            placeholder="Tuliskan detail/ciri-ciri umum barang"
            class="mt-2 h-[89px] w-full rounded-[10px] border border-tt-outline p-3 text-sm placeholder:text-tt-outline"></textarea>

        <!-- Tanggal (dengan ikon kalender) & lokasi (dropdown) -->
        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block font-display text-base font-semibold">Tanggal dan waktu kejadian*</label>
                <div class="relative mt-2">
                    <!-- Ikon kalender bawaan browser dibuat transparan, posisinya tetap di kanan
                         sehingga saat ikon Figma di atasnya diklik, pemilih tanggal tetap terbuka -->
                    <input
                        type="datetime-local"
                        name="tanggal_waktu_kejadian"
                        required
                        class="h-12 w-full rounded-[10px] border border-tt-outline bg-white px-3 text-sm">
                    <img src="<?= base_url('assets/images/icon-kalender.svg') ?>" alt="" class="pointer-events-none absolute right-3 top-3.5 h-[21px] w-[22px]">
                </div>
            </div>
            <div>
                <label class="block font-display text-base font-semibold">
                    Lokasi Kejadian*
                </label>

                <input
                    type="text"
                    name="lokasi_kejadian"
                    placeholder="Contoh: Depan Lab Komputer Gedung Teknik Informasi"
                    maxlength="200"
                    required
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm placeholder:text-tt-outline">
            </div> <!-- Penutup kolom lokasi -->
        </div> <!-- Penutup grid tanggal dan lokasi -->

        <!-- Upload dan pos penyerahan -->

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2" id="upload-fields">

            <!-- Foto -->
            <div id="foto-field" class="md:col-span-2">
                <label class="block font-display text-base font-semibold" id="label-foto">
                    Foto barang (opsional)
                </label>

                <input
                    type="file"
                    name="foto[]"
                    id="foto"
                    multiple
                    accept=".jpg,.jpeg,.png,.webp,.pdf"
                    class="mt-2 w-full rounded-[10px] border border-tt-outline p-3 text-sm">

                <p class="mt-2 text-xs">
                    JPG, PNG, WebP, PDF maksimal 5 MB.
                </p>
            </div>

            <!-- Pos penyerahan -->
            <div id="pos-field" class="hidden">
                <label class="block font-display text-base font-semibold">
                    Titik penyerahan barang temuan*
                </label>

                <select
                    name="id_pos"
                    id="id-pos"
                    class="mt-2 h-12 w-full rounded-[10px] border border-tt-outline px-3 text-sm"
                    disabled>
                    <option value="">Pilih Pos Penyerahan</option>

                    <?php foreach ($posAktif as $pos): ?>
                        <option value="<?= (int) $pos['id_pos'] ?>">
                            <?= h($pos['nama_pos']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

        </div>
        <!-- Tombol -->
        <div class="mt-4 flex gap-2.5 p-2.5">
            <button
                type="submit"
                class="h-[43px] rounded-[10px] bg-tt-navy px-[11px] text-sm font-semibold text-white hover:bg-tt-orange hover:text-tt-navy">
                Kirim Laporan
            </button>

            <a
                href="<?= h($linkTutup) ?>"
                class="flex h-[43px] items-center rounded-[10px] border border-tt-navy px-[11px] text-sm font-semibold hover:bg-indigo-100">
                Batal
            </a>
        </div> <!-- Penutup area tombol -->

    </form>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisSelect = document.getElementById('jenis-laporan');
        const posField = document.getElementById('pos-field');
        const posSelect = document.getElementById('id-pos');
        const fotoField = document.getElementById('foto-field');
        const fotoInput = document.getElementById('foto');
        const fotoLabel = document.getElementById('label-foto');

        if (
            !jenisSelect ||
            !posField ||
            !posSelect ||
            !fotoField ||
            !fotoInput ||
            !fotoLabel
        ) {
            return;
        }

        function updateForm(updateUrl = false) {
            const isTemuan = jenisSelect.value === 'temuan';

            posField.classList.toggle('hidden', !isTemuan);
            posSelect.disabled = !isTemuan;
            posSelect.required = isTemuan;

            fotoInput.required = isTemuan;
            fotoField.classList.toggle('md:col-span-2', !isTemuan);

            fotoLabel.textContent = isTemuan ?
                'Foto barang (maksimal 3)*' :
                'Foto barang (opsional)';

            // Sinkronisasi URL dengan jenis laporan.
            if (updateUrl) {
                const url = new URL(window.location.href);

                url.searchParams.set(
                    'modal',
                    isTemuan ? 'temuan' : 'hilang'
                );

                window.history.replaceState({}, '', url.toString());
            }
        }

        jenisSelect.addEventListener('change', function() {
            updateForm(true);
        });

        updateForm(false);
        // Jalankan ketika dropdown berubah.
        jenisSelect.addEventListener('change', updateForm);

        // Jalankan juga ketika modal pertama kali dibuka.
        updateForm();
    });
</script>