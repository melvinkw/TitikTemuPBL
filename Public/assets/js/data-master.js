
document.addEventListener('DOMContentLoaded', () => {

    // mengambil data dari php
    const masterData = window.masterData || {};
    const masterTabs = window.masterTabs || {};

    // mengambil elemen html
    const tabs = document.querySelectorAll('.master-tab');
    const tableBody = document.getElementById('master-table-body');
    const tableTitle = document.getElementById('table-title');
    const prevButton = document.getElementById('prev-page');
    const nextButton = document.getElementById('next-page');
    const pageNumbers = document.getElementById('page-numbers');

    // pengaturan pagination
    const rowsPerPage = 8;
    let activeTab = 'kategori';
    let currentPage = 1;

    // judul tabel berdasarkan tab aktif
    const tableTitles = {
        kategori: 'Kategori Barang',
        lokasi: 'Lokasi Master',
        pos: 'Pos Penyerahan'
    };

    // mengambil data sesuai tab aktif
    function getCurrentData() {
        return masterData[activeTab] || [];
    }

    // menghitung jumlah halaman
    function getTotalPages() {
        const data = getCurrentData();

        return Math.max(
            1,
            Math.ceil(data.length / rowsPerPage)
        );
    }

    // menampilkan data ke dalam tabel
    function renderTable() {

        const data = getCurrentData();
        const totalPages = getTotalPages();

        // memastikan halaman tetap dalam batas yang tersedia
        if (currentPage > totalPages) {
            currentPage = totalPages;
        }

        const startIndex = (currentPage - 1) * rowsPerPage;
        const endIndex = startIndex + rowsPerPage;
        const currentRows = data.slice(startIndex, endIndex);

        // memperbarui judul tabel
        tableTitle.textContent =
            tableTitles[activeTab] ||
            masterTabs[activeTab] ||
            'Data Master';

        tableBody.innerHTML = '';

        // menampilkan pesan jika data kosong
        if (currentRows.length === 0) {

            tableBody.innerHTML = `
                <tr>
                    <td
                        colspan="3"
                        class="px-5 py-10 text-center text-slate-500">
                        Belum ada data.
                    </td>
                </tr>
            `;

            renderPagination();
            return;
        }

        // menampilkan setiap baris data
        currentRows.forEach((row) => {

            const tr = document.createElement('tr');

            tr.className =
                'transition-colors duration-150 hover:bg-slate-50';

            // menentukan warna berdasarkan status
            const statusClass =
                row.status === 'Aktif'
                    ? 'bg-[#E6F7ED] text-[#17633F]'
                    : 'bg-[#F1F1F1] text-[#757575]';

            tr.innerHTML = `
                <td class="px-5 py-4">
                    ${escapeHtml(row.nama)}
                </td>

                <td class="px-5 py-4">
                    ${escapeHtml(row.deskripsi)}
                </td>

                <td class="px-5 py-4">
                    <span
                        class="inline-flex rounded-full px-3 py-2 text-xs font-semibold ${statusClass}">
                        ${escapeHtml(row.status)}
                    </span>
                </td>
            `;

            tableBody.appendChild(tr);
        });

        renderPagination();
    }

    // mengatur tampilan dan navigasi pagination
    function renderPagination() {

        const totalPages = getTotalPages();

        pageNumbers.innerHTML = '';

        // mengatur kondisi tombol sebelumnya dan berikutnya
        prevButton.disabled = currentPage === 1;
        nextButton.disabled = currentPage === totalPages;

        let pages = [];

        // menentukan angka halaman yang ditampilkan
        if (totalPages <= 4) {

            for (let page = 1; page <= totalPages; page++) {
                pages.push(page);
            }

        } else if (currentPage <= 3) {

            pages = [1, 2, 3, '...', totalPages];

        } else if (currentPage >= totalPages - 2) {

            pages = [
                1,
                '...',
                totalPages - 2,
                totalPages - 1,
                totalPages
            ];

        } else {

            pages = [
                1,
                '...',
                currentPage - 1,
                currentPage,
                currentPage + 1,
                '...',
                totalPages
            ];
        }

        // membuat tombol untuk setiap halaman
        pages.forEach((page) => {

            if (page === '...') {

                const dots = document.createElement('span');

                dots.className = `
                    flex h-8 min-w-8
                    items-center justify-center
                    px-1
                    text-xs font-semibold
                    text-tt-navy
                `;

                dots.textContent = '...';
                pageNumbers.appendChild(dots);

                return;
            }

            const button = document.createElement('button');

            button.type = 'button';
            button.dataset.page = page;
            button.textContent = page;

            // memberikan tampilan berbeda pada halaman aktif
            if (page === currentPage) {

                button.className = `
                    flex h-8 min-w-8
                    items-center justify-center
                    rounded-lg
                    bg-[#DCE3FD]
                    px-2
                    text-xs font-semibold
                    text-tt-navy
                    transition-all
                    duration-150
                `;

                button.setAttribute('aria-current', 'page');

            } else {

                button.className = `
                    flex h-8 min-w-8
                    items-center justify-center
                    rounded-lg
                    px-2
                    text-xs font-semibold
                    text-tt-navy
                    transition-all
                    duration-150
                    hover:bg-tt-navy/15
                `;
            }

            // berpindah ke halaman yang dipilih
            button.addEventListener('click', () => {
                currentPage = Number(page);
                renderTable();
            });

            pageNumbers.appendChild(button);
        });
    }

    // navigasi ke halaman sebelumnya
    prevButton.addEventListener('click', () => {

        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });

    // navigasi ke halaman berikutnya
    nextButton.addEventListener('click', () => {

        const totalPages = getTotalPages();

        if (currentPage < totalPages) {
            currentPage++;
            renderTable();
        }
    });

    // mengatur perpindahan tab
    tabs.forEach((tab) => {

        tab.classList.add(
            'transition-all',
            'duration-150',
            'hover:-translate-y-0.5',
            'hover:shadow-sm'
        );

        tab.addEventListener('click', () => {

            activeTab = tab.dataset.tab;
            currentPage = 1;

            // memperbarui tampilan tab aktif
            tabs.forEach((item) => {

                item.classList.remove(
                    'border-tt-orange',
                    'bg-tt-orange'
                );

                item.classList.add(
                    'border-tt-outline',
                    'bg-white'
                );
            });

            tab.classList.remove(
                'border-tt-outline',
                'bg-white'
            );

            tab.classList.add(
                'border-tt-orange',
                'bg-tt-orange'
            );

            renderTable();
        });
    });

    // mengamankan teks sebelum dimasukkan ke html
    function escapeHtml(value) {

        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // menampilkan data saat halaman pertama kali dibuka
    renderTable();

});
