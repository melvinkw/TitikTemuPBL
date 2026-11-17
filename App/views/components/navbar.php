<?php
/**
 * App/views/components/navbar.php
 * Navbar bersama untuk semua role yang sudah login (mahasiswa, petugas, admin).
 * Figma: komponen "Nav" — menu di tengah, Logout di kanan.
 * Di HP (di bawah md) menu dilipat ke tombol ☰ memakai <details>, jadi tidak butuh JavaScript.
 *
 * Jangan dipanggil langsung dari halaman. Pakai navbar per role:
 *   App/views/components/mahasiswa/navbar.php
 *   App/views/components/petugas/navbar.php
 *   App/views/components/admin/navbar.php
 *
 * Variabel yang disiapkan navbar per role:
 *   $daftarMenu = ['kunci' => ['Label', 'alamat/relatif.php'], ...];
 *   $menuAktif  = 'kunci';   // menu yang sedang dibuka (warna oranye)
 */
$daftarMenu = $daftarMenu ?? [];
$menuAktif = $menuAktif ?? '';
?>
<nav class="relative flex min-h-19 items-center justify-between gap-3 border-b-[0.5px] border-tt-outline px-4 py-3 md:px-11">
    <!-- Spacer kiri supaya menu tetap di tengah (sama lebar dengan tombol Logout) -->
    <div class="hidden w-18 md:block"></div>

    <!-- Menu di layar besar -->
    <div class="hidden flex-1 flex-wrap items-center justify-center gap-x-5.5 md:flex">
        <?php foreach ($daftarMenu as $kunci => $menu): ?>
            <a href="<?= h(base_url($menu[1])) ?>"
               class="p-2.5 text-sm whitespace-nowrap <?= $menuAktif === $kunci ? 'font-extrabold text-tt-orange' : 'font-semibold text-tt-navy hover:text-tt-orange' ?>"
               <?= $menuAktif === $kunci ? 'aria-current="page"' : '' ?>>
                <?= h($menu[0]) ?>
            </a>
        <?php endforeach; ?>
    </div>

    <!-- Menu di HP: tombol ☰ membuka daftar menu -->
    <details class="group md:hidden">
        <summary class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-tt-outline [&::-webkit-details-marker]:hidden"
                 aria-label="Buka menu">
            <!-- Ikon garis tiga (tertutup) / silang (terbuka) -->
            <svg class="h-5 w-5 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
            <svg class="hidden h-5 w-5 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
                <path d="M6 6l12 12M18 6L6 18" />
            </svg>
        </summary>

        <div class="absolute inset-x-0 top-full z-30 flex flex-col border-b-[0.5px] border-tt-outline bg-tt-primary px-4 pb-3 shadow-sm">
            <?php foreach ($daftarMenu as $kunci => $menu): ?>
                <a href="<?= h(base_url($menu[1])) ?>"
                   class="rounded-lg px-2.5 py-3 text-sm <?= $menuAktif === $kunci ? 'font-extrabold text-tt-orange' : 'font-semibold text-tt-navy hover:bg-white/60' ?>"
                   <?= $menuAktif === $kunci ? 'aria-current="page"' : '' ?>>
                    <?= h($menu[0]) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </details>

    <!-- Logout dikirim lewat POST + token CSRF -->
    <form method="post" action="<?= h(base_url('logout.php')) ?>" class="flex w-18 justify-end">
        <?= csrf_field() ?>
        <button type="submit" class="cursor-pointer p-2.5 text-sm font-semibold whitespace-nowrap text-tt-coral hover:text-tt-orange">
            Logout
        </button>
    </form>
</nav>
