<?php
/**
 * App/views/components/header.php
 * Bagian atas HTML yang dipakai semua halaman (semua role).
 *
 * Cara pakai di halaman:
 *   $pageTitle = 'Dashboard';   // judul tab browser
 *   require_once APP_ROOT . '/App/views/components/header.php';
 *
 * Ditutup oleh App/views/components/footer.php.
 */
$pageTitle = $pageTitle ?? 'TitikTemu';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= h($pageTitle) ?> — TitikTemu</title>

    <!-- Font: Inter (teks biasa) & League Spartan (judul) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&family=League+Spartan:wght@600;700;800&display=swap" rel="stylesheet">

    <!-- CSS hasil build Tailwind (npm run dev / npm run build) -->
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
</head>

<body class="bg-tt-primary font-body text-tt-navy antialiased">
    <div class="mx-auto flex min-h-screen max-w-360 flex-col">
