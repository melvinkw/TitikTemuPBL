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
            <a href="Laporan-Masuk.php" >Laporan Masuk</a>
            <a href="#" >Lihat Data Master</a>
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

        <!-- Grid Layout Utama (Kiri: Detail & Foto, Kanan: Status Laporan) -->
        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mb-12">
            
            <!-- KOLOM KIRI: Informasi Barang & Foto Barang -->
            <div class="lg:col-span-2 bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
                
                <!-- Badge Kategori -->
                <div class="mb-2">
                    <span class="inline-block px-1 py-2 bg-[#EBE5F2] text-[#8A38F5] text-[12px] font-Inter font-[600] rounded-full">
                        Elektronik
                    </span>
                </div>

                <!-- Nama Barang -->
                <h1 class="text-[28px] font-spartan font-[700] text-[#02103D] mb-4">TWS Huawei</h1>

                <!-- Detail -->
                <div class="space-y-1.5 text-[14px] font-Inter font-[400] text-[#02103D] mb-4">
                    <p>LPR 8</p>
                    <p>24 Oktober 2026 12:45</p>
                    <p>John Doe</p>
                </div>

                <!-- Bagian Foto Barang -->
                <div>
                    <h2 class="text-[28px] font-spartan font-[700] text-[#02103D] mb-4">Foto Barang</h2>
                    
                    <!-- Grid Foto / Image Gallery -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <!-- Foto Utama -->
                        <div class="rounded-xl overflow-hidden bg-slate-100 h-full min-h-[320px]
                        ">
                            <img src="https://images.unsplash.com/photo-1590658268037-6bf12165a8df?auto=format&fit=crop&w=500&q=80" alt="Foto 1" class="w-full h-full object-cover">
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
                    
                    <!-- BAGIAN TINDAKAN / PEMERIKSAAN ADMIN -->
                    <div class="mt-8">
                        <h2 class="text-[28px] font-spartan font-bold text-[#02103D] mb-4">Tindakan</h2>
                        
                        <!-- Card Pembungkus Form Tindakan -->
                        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200">
                            <h3 class="text-[18px] font-spartan font-bold text-[#02103D] mb-1">Pemeriksaan Petugas / Admin</h3>
                            <p class="text-[13px] text-slate-500 mb-6">Lorem ipsum dolor sit amet, consectetur adipiscing elit</p>

                            <form action="#" method="POST" class="space-y-5">

                    <!-- Keputusan Dropdown -->
                    <div>
                        <label class="block text-[14px] font-semibold text-[#02103D] mb-2">
                            Keputusan<span class="text-red-500">*</span>
                        </label>
                        <div class="relative w-full border border-slate-300 rounded-xl px-4 py-3 text-sm text-[#02103D] bg-white flex justify-between items-center cursor-pointer">
                            <span class="text-slate-400">Pilih Keputusan</span>
                            <svg class="w-4 h-4 text-[#02103D]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"></path>
                            </svg>
                        </div>
                    </div>

                    <!-- Catatan -->
                    <div>
                        <label class="block text-[14px] font-semibold text-[#02103D] mb-2">
                            Catatan (optional)
                        </label>
                        <textarea rows="4" placeholder="Tuliskan catatan tambahan di sini..." class="w-full rounded-xl border border-slate-300 px-4 py-3 text-sm focus:outline-none focus:border-[#8A38F5] text-slate-700 resize-none"></textarea>
                    </div>

                    <!-- Simpan Keputusan -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-3 px-6 bg-[#02103D] hover:bg-[#FA9B2D] text-white font-semibold rounded-xl text-sm transition-colors shadow-sm">
                            Simpan Keputusan
                        </button>
                    </div>
                </form>
            </div>
        </div>
                </div>

            </div>

            <!-- Status Laporan -->
            <div class="lg:col-span-2">
                <!-- Card Status Laporan -->
                <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100">
                    <h2 class="text-[28px] font-spartan font-[700] text-[#02103D] mb-4">Status Laporan</h2>
                    
                    <!-- Badge Status -->
                    <div class="flex items-center gap-2 mb-3">
                        <span class="inline-block px-1.5 py-2 bg-[#FFF7DD] text-[#7A5605] text-[12px] font-Inter font-[600] rounded-full">
                            Menunggu Pemeriksaan
                        </span>
                    </div>

                    <p class="text-[12px] font-Inter font-[400] text-[#02103D]">Laporan dibuat pada 29-10-2026 13:57.</p>
                </div>
            </div>

        </div>

        <!-- Riwayat Status Laporan -->
        <h2 class="text-[20px] font-spartan font-[600] text-[#02103D] mb-8 px-1">Riwayat Status Laporan</h2>

        <!-- Tabel Riwayat -->
        <div class="overflow-x-auto border border-[#8795C7] bg-[#FFFFFF] rounded-xl">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b border-[#8795C7] bg-[#E9EDFF] text-[18px] font-spartan font-[600] text-[#02103D]">
                        <th class="py-3 px-10">Waktu</th>
                        <th class="py-3 px-20">Perubahan</th>
                        <th class="py-3 px-30">Diubah Oleh</th>
                        <th class="py-3 px-40">Catatan</th>
                    </tr>
                </thead>
                <tbody class="text-[14px] font-Inter font-[400] text-[#02103D] divide-y divide-slate-100">
                    <tr>
                        <td class="py-3.5 px-10 font-[400]">27-10-2026 13:01</td>
                        <td class="py-3.5 px-20 flex items-center gap-2">
                            Status Awal -> 
                            <!-- Badge Mini Status di dalam Tabel -->
                            <span class=" inline-block py-2.5 px-1.5 bg-[#FFF7DD] text-[#7A5605] text-[10px] font-Inter font-[600] rounded-full">
                                Menunggu Pemeriksaan
                            </span>
                        </td>
                        <td class="py-3.5 px-15">Admin 1</td>
                        <td class="py-3.5 px-40 text-[#02103D]">Status ditetapkan melalui pemeriksaan petugas.</td>
                    </tr>
                </tbody>
            </table>
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