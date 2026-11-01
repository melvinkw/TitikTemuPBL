-- TitikTemu migration file. Do not edit after it has been applied to a shared database.
-- For future changes, create a new numbered file (example: 003_add_column_x.sql).

-- Data master awal. Aman dijalankan berulang kali karena menggunakan ON CONFLICT.
INSERT INTO kategori(nama_kategori,deskripsi) VALUES
 ('Elektronik','Perangkat elektronik dan aksesori'),
 ('Dokumen','KTM, kartu, surat, dan dokumen'),
 ('Kunci','Kunci kendaraan atau ruangan'),
 ('Tas dan perlengkapan','Tas, dompet, tempat pensil, dan sejenisnya'),
 ('Pakaian dan aksesori','Jaket, topi, jam tangan, dan aksesori'),
 ('Alat tulis','Pulpen, buku, dan perlengkapan tulis'),
 ('Lainnya','Barang di luar kategori lain')
ON CONFLICT(nama_kategori) DO NOTHING;
INSERT INTO lokasi(nama_lokasi,deskripsi) VALUES
 ('Ruang kelas','Ruang perkuliahan'),
 ('Laboratorium','Ruang praktik dan laboratorium'),
 ('Lobi atau lift','Area lobi dan lift gedung'),
 ('Musala','Area musala'),
 ('Kantin','Area kantin'),
 ('Gazebo','Area gazebo'),
 ('Parkiran','Area parkir'),
 ('Ruang administrasi JTI','Ruang administrasi jurusan'),
 ('Area kampus lainnya','Area kampus lainnya')
ON CONFLICT(nama_lokasi) DO NOTHING;
COMMIT;
-- Titik penyerahan tidak dibuat otomatis karena nama/lokasi resminya harus ditetapkan tim.
-- Tambahkan dari halaman Admin > Data Master > Pos Penyerahan.
