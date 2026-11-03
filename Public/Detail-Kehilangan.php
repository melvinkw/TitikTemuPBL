<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TitikTemu - Detail Temuan (Public)</title>
    
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
        <nav class="flex items-center gap-6 text-sm font-semibold text-[#02103D]">
            <a href="#" >Beranda</a>
            <a href="#" >Daftar Barang</a>
            <span class="text-[#8795C7]">|</span>
            <a href="#" class="px-5 py-2 bg-[#02103D] hover:bg-[#FA9B2D] text-white text-xs font-semibold rounded-xl transition-colors shadow-sm">
                Login
            </a>
        </nav>
    </header>

    <!-- BODY HALAMAN PUBLIC DETAIL -->
    <main class="max-w-[1500px] w-full mx-auto py-2.5 px-6 flex-grow">
        
        <!-- Tombol Kembali -->
        <div class="mb-6 mt-4">
            <a href="#" class="text-[14px] font-semibold text-[#02103D] hover:text-[#FA9B2D] hover:underline flex items-center gap-1 w-fit">
                &lt;&lt; Kembali ke daftar
            </a>
        </div>

        <!-- Grid Layout Utama (Kiri: Detail & Foto, Kanan: Status & Peringatan Login) -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-12">
            
            <!-- KOLOM KIRI: Informasi Barang & Foto Barang -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
                
                <!-- Badge Kategori -->
                <div class="mb-3">
                    <span class="inline-block px-3 py-1 bg-[#EBE5F2] text-[#8A38F5] text-[12px] font-semibold rounded-full">
                        Elektronik
                    </span>
                </div>

                <!-- Nama Barang -->
                <h1 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">TWS Huawei</h1>

                <!-- Detail -->
                <div class="space-y-1.5 text-[14px] font-normal text-[#02103D] mb-8">
                    <p>LPR 8</p>
                    <p>24 Oktober 2026 12:45</p>
                    <p>John Doe</p>
                </div>

                <!-- Bagian Foto Barang -->
                <div>
                    <h2 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">Foto Barang</h2>
                    
                    <!-- Grid Foto / Image Gallery -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Foto Utama -->
                        <div class="rounded-xl overflow-hidden bg-slate-100 h-full min-h-[320px]">
                            <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Foto TWS Huawei 1" class="w-full h-full object-cover">
                        </div>
                        <!-- Foto Tambahan -->
                        <div class="flex flex-col gap-4">
                            <div class="rounded-xl overflow-hidden bg-slate-100 h-[155px]">
                                <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=300&q=80" alt="Foto TWS Huawei 2" class="w-full h-full object-cover">
                            </div>
                            <div class="rounded-xl overflow-hidden bg-slate-100 h-[155px]">
                                <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=300&q=80" alt="Foto TWS Huawei 3" class="w-full h-full object-cover">
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- KOLOM KANAN: Status Laporan & Peringatan Login -->
            <div class="lg:col-span-2">
                <!-- Card Status Laporan -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">Status Laporan</h2>
                    
                    <!-- Badge Status -->
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-block px-1.5 py-2 bg-[#E6F7ED] text-[#17633F] text-[12px] font-Inter font-[600] rounded-full">
                            Aktif
                        </span>
                    </div>

                    <p class="text-[12px] font-normal text-[#02103D] mb-6">Laporan dibuat pada 29-10-2026 13:57.</p>
                </div>
            </div>
        </div>
    </main>

    <!-- BAGIAN FOOTER (Dibuat oleh temanmu) -->
    <footer class="w-full bg-[#DCE3FD] py-4 px-8 text-xs text-[#8795C7] flex justify-between items-center border-t border-[#8795C7]">
        <div><strong class="text-[#8795C7]">TitikTemu.</strong> Politeknik Negeri Malang</div>
        <div>Platform informasi barang hilang dan barang temuan.</div>
        <div>&copy; 2026 TitikTemu</div>
    </footer>

</body>
</html>