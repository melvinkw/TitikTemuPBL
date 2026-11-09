<?php

$currentPage = basename($_SERVER['PHP_SELF']);

$navItems = [
    [
        'label' => 'Dashboard',
        'href' => base_url('petugas/dashboard.php'),
        'page' => 'dashboard.php',
        'available' => true
    ],
    [
        'label' => 'Laporan Masuk',
        'href' => base_url('petugas/laporan/Laporan-Masuk.php'),
        'page' => 'Laporan-Masuk.php',
        'available' => true
    ],
    [
        'label' => 'Lihat Data Master',
        'href' => base_url('petugas/data-master.php'),
        'page' => 'data-master.php',
        'available' => true
    ],
    [
        'label' => 'Rekapitulasi',
        'href' => base_url('petugas/rekapitulasi.php'),
        'page' => 'rekapitulasi.php',
        'available' => true
    ],
];


?>

<header class="sticky top-0 z-30 border-b border-tt-outline bg-tt-primary">

    <nav class="mx-auto flex min-h-[76px] max-w-[1440px] items-center justify-between gap-6 px-5 py-3 sm:px-8 lg:px-11">

        <!-- Menu Burger -->
        <button id="menu-toggle" class="inline-flex h-10 w-10 items-center justify-center rounded-lg border border-tt-outline md:hidden" type="button" aria-label="Buka menu" aria-expanded="false" aria-controls="main-menu">
            <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2">
                <path d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>

        <!-- Navigation Links -->
        <div id="main-menu" class="absolute left-0 right-0 top-full hidden flex-col border-b border-tt-outline bg-tt-primary px-5 pb-4 shadow-sm md:static md:flex md:flex-1 md:flex-row md:items-center md:justify-center md:gap-3 md:border-0 md:bg-transparent md:px-0 md:pb-0 md:shadow-none">

            <?php foreach ($navItems as $item): ?>

                <?php
                $isActive = $currentPage === $item['page'];
                ?>

                <a
                    href="<?= h($item['href']) ?>"
                    class="rounded-lg px-3 py-3 text-sm font-semibold transition hover:bg-white/60 <?= $isActive ? 'text-tt-orange' : 'text-tt-navy' ?>"
                    <?= $isActive ? 'aria-current="page"' : '' ?>>
                    <?= h($item['label']) ?>
                </a>

            <?php endforeach; ?>

        </div>

        <!-- Logout -->
        <form method="post" action="<?= h(base_url('logout.php')) ?>" class="shrink-0">

            <?= csrf_field() ?>

            <button
                type="submit"
                class="rounded-lg px-2 py-2 text-sm font-semibold text-tt-coral transition hover:bg-white/50">
                Logout
            </button>

        </form>

    </nav>

</header>

<!-- Mobile Menu Script -->
<script>
    const menuToggle = document.getElementById('menu-toggle');
    const mainMenu = document.getElementById('main-menu');

    menuToggle?.addEventListener('click', () => {

        const isExpanded =
            menuToggle.getAttribute('aria-expanded') === 'true';

        menuToggle.setAttribute(
            'aria-expanded',
            String(!isExpanded)
        );

        mainMenu?.classList.toggle('hidden', isExpanded);
        mainMenu?.classList.toggle('flex', !isExpanded);

    });
</script>