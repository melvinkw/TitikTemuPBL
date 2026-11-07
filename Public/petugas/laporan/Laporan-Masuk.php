<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TitikTemu - Detail Barang</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=League+Spartan:wght@600;700&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
    theme: {
        extend: {
        fontFamily: {
                sans: ['Inter', 'sans-serif'],
                spartan: ['"League Spartan"', 'sans-serif'],
                }
            }
        }
    }
</script>
</head>
<body class="bg-[#DCE3FD] min-h-screen flex flex-col justify-between font-sans text-slate-800">

    <!-- BAGIAN NAVBAR -->
    <header class="w-full bg-[#DCE3FD] py-4 px-8 flex justify-between items-center border-b border-[#8795C7]">
        <div class="text-sm font-semibold text-[#02103D]"></div>
        <nav class="flex gap-6 text-sm font-semibold text-[#02103D]">
            <a href="#" >Dashboard</a>
            <a href="#" >Laporan Masuk</a>
            <a href="Laporan-Masuk.php" >Lihat Data Master</a>
            <a href="#" >Rekapitulasi</a>
        </nav>
        <span class="text-sm font-medium text-[#EC745C] cursor-pointer">Logout</span>
    </header>

    <!-- BODY HALAMAN DETAIL LAPORAN -->
    <main class="max-w-[1500px] w-full mx-auto mt-2 mb-2 py-2.5 flex-grow">
        
        <div class="mt-5 px-5 text-[14px] font-Inter font-[600] tracking-wider text-[#02103D]">
            <p class="text-[14px] font-Inter font-[600] uppercase">Ruang Kerja Petugas</p>
            <p class="mb-2 text-[32px] font-spartan font-[800]">Daftar Barang</p>
            <p class="mb-10 text-[12px] font-Inter font-[400]">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.</p>
        </div>

        <!-- KOMPONEN: GRID DAFTAR BARANG (CARD GRID)   -->
        <div class=" grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-12 px-10">

            <!-- CARD 1 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <!-- Gambar dengan Badge Overlay -->
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4 justify-center">
                        <!-- Badge Jenis Laporan (Barang Temuan - Biru) -->
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#E5F3FE] text-[#008BFB] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Barang Temuan
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <!-- Detail Barang -->
                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-8">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <!-- Tombol Link Detail -->
                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D] ">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 2-->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#FFF4EE] text-[#E48F31] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Kehilangan Barang
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 3 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <!-- Badge Jenis Laporan (Kehilangan Barang - Oranye Krem) -->
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#FFF4EE] text-[#E48F31] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Kehilangan Barang
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 4 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <!-- Badge Jenis Laporan (Kehilangan Barang - Oranye Krem) -->
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#E5F3FE] text-[#008BFB] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Barang Temuan
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 5 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <!-- Badge Jenis Laporan (Kehilangan Barang - Oranye Krem) -->
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#FFF4EE] text-[#E48F31] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Kehilangan Barang
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 6 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <!-- Badge Jenis Laporan (Kehilangan Barang - Oranye Krem) -->
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#FFF4EE] text-[#E48F31] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Kehilangan Barang
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 7 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#E5F3FE] text-[#008BFB] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Barang Temuan
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>

            <!-- CARD 8 -->
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-[#8795C7] flex flex-col justify-between">
                <div>
                    <div class="relative w-full h-[218px] rounded-xl overflow-hidden bg-slate-100 mb-4">
                        <span class="absolute top-3 left-3 px-1 py-2 bg-[#E5F3FE] text-[#008BFB] text-[12px] font-Inter font-[600] rounded-full shadow-sm z-10">
                            Barang Temuan
                        </span>
                        <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Tumbler Hitam" class="w-full h-full object-cover">
                    </div>

                    <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Tumbler Hitam</h3>
                    <p class="text-[13px] text-slate-500 mb-3 line-clamp-1">Ini tumbler warna hitam pekat</p>
                    
                    <div class="space-y-1 text-[13px] text-[#02103D] mb-4">
                        <p class="font-normal">LPR 8</p>
                        <p class="text-slate-500">24 Oktober 2026 12:45</p>
                    </div>
                </div>

                <div>
                    <a href="Detail-Laporan.php" class="inline-flex items-center gap-1 text-[13px] font-semibold text-[#02103D] hover:text-[#FA9B2D]">
                        Lihat Detail &rarr;
                    </a>
                </div>
            </div>
        </div>

    </main>

    <!-- BAGIAN FOOTER -->
    <footer class="w-full bg-[#DCE3FD] py-4 px-8 text-xs text-[#8795C7] flex justify-between items-center border-t border-[#8795C7]">
        <div><strong class="text-[#8795C7]">TitikTemu.</strong> Politeknik Negeri Malang</div>
        <div>Platform informasi barang hilang dan barang temuan.</div>
        <div>&copy; 2026 TitikTemu</div>
    </footer>
</body>
</html>