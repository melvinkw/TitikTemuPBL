# PBL_TitikTemu — konteks project

Repo: `https://github.com/melvinkw/TitikTemuPBL.git`, branch kerja `develop`.

## Alur kerja wajib: rencana → konfirmasi → eksekusi → dokumentasi

1. **Pahami.** Baca permintaan, baca kode yang relevan. Jangan menyentuh file dulu.
2. **Ulangi pemahaman.** Tulis kembali dalam bahasa sederhana: apa yang diminta,
   file mana yang akan disentuh, dan asumsi yang diambil.
3. **Ajukan rencana.** Sebutkan langkah konkret, risiko, dan hal yang tidak akan
   disentuh. Tunggu jawaban operator.
4. **Baru eksekusi.** Setelah operator setuju, kerjakan. Hook akan meminta satu
   kali persetujuan klik sebelum tulisan pertama di repo ini.
5. **Dokumentasikan.** Lihat bagian di bawah.

Kalau di tengah jalan ternyata rencananya salah, berhenti dan laporkan — jangan
melebar sendiri.

## Dokumentasi (WAJIB)

Setiap kali mengubah, menambah, atau menghapus **apa pun** di repo ini, buat entri
di `docs/agent-log/YYYY-MM-DD_<slug>/` sebelum menyelesaikan giliran. Format lengkap
ada di `docs/agent-log/README.md`. Ringkasnya, entri itu harus memuat:

- apa yang dikerjakan dan alasannya,
- tabel perbedaan sebelum vs sesudah per file,
- daftar file yang disentuh beserta aksinya,
- perintah verifikasi dan hasilnya,
- **cara mengembalikan** — `changes.patch` yang lolos `git apply --check -R`,
- risiko atau hal yang belum selesai.

Lalu perbarui `docs/agent-log/INDEX.md` (terbaru di atas).

Hook `~/.omp/agent/hooks/pre/pbl-change-log.ts` menegakkan ini: kalau working tree
berubah dalam satu sesi tanpa entri baru, agent ditolak menyelesaikan giliran.

## Larangan

- **Jangan menyentuh git atau GitHub.** Tidak ada `commit`, `push`, `add`, `reset`,
  `clean`, `checkout`, `rebase`, `stash`, `tag`, `merge`, dan tidak ada `gh`.
  Hanya git baca-saja. Urusan repo sepenuhnya milik operator.
- Jangan pernah menyalin atau menulis isi `.env` ke dokumentasi, chat, atau commit.
- Jangan menyentuh file di luar repo ini.
- Jangan meninggalkan file uji coba. Kalau membuat scratch untuk verifikasi, hapus
  setelah selesai dan sebutkan di entri dokumentasi.

## Stack

PHP >= 8.1, PDO **PostgreSQL** (`ext-pdo_pgsql`), PHPMailer, phpdotenv, Supabase storage,
Tailwind lewat `package.json`. Struktur: `App/` (actions, queries, services, views),
`Public/`, `Database/`, `resources/`.
