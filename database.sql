-- =====================================================
-- DATABASE: SISTEM PAKAR PENYAKIT GIGI
-- METODE: FORWARD CHAINING
-- PRAKTIK MANDIRI DRG. HJ. RINI SUTARTI
-- =====================================================

CREATE DATABASE IF NOT EXISTS `db_gigi` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_gigi`;

-- =====================================================
-- TABEL PENGGUNA (ADMIN & USER)
-- Menyimpan data pengguna yang bisa login ke sistem
-- =====================================================
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `nama` VARCHAR(100) NOT NULL,
    `username` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `role` ENUM('admin','user') NOT NULL DEFAULT 'user',
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABEL PENYAKIT
-- Menyimpan daftar penyakit gigi yang dapat didiagnosa
-- =====================================================
CREATE TABLE IF NOT EXISTS `penyakit` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode` VARCHAR(10) NOT NULL UNIQUE,
    `nama` VARCHAR(150) NOT NULL,
    `deskripsi` TEXT,
    `solusi` TEXT,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABEL GEJALA
-- Menyimpan daftar gejala yang mungkin dialami pasien
-- =====================================================
CREATE TABLE IF NOT EXISTS `gejala` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `kode` VARCHAR(10) NOT NULL UNIQUE,
    `nama` VARCHAR(200) NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- TABEL ATURAN (RULES) - BASIS PENGETAHUAN
-- Relasi antara penyakit dan gejalanya (IF gejala THEN penyakit)
-- Inilah inti dari metode Forward Chaining
-- =====================================================
CREATE TABLE IF NOT EXISTS `aturan` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `penyakit_id` INT NOT NULL,
    `gejala_id` INT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (`penyakit_id`) REFERENCES `penyakit`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gejala_id`) REFERENCES `gejala`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_rule` (`penyakit_id`, `gejala_id`)
) ENGINE=InnoDB;

-- =====================================================
-- TABEL RIWAYAT KONSULTASI
-- Menyimpan histori diagnosa pasien
-- =====================================================
CREATE TABLE IF NOT EXISTS `konsultasi` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NULL,
    `nama_pasien` VARCHAR(100) NOT NULL,
    `tanggal` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    `hasil_diagnosa` TEXT,
    `persentase` DECIMAL(5,2) DEFAULT 0
) ENGINE=InnoDB;

-- =====================================================
-- TABEL DETAIL KONSULTASI
-- Menyimpan gejala yang dipilih saat konsultasi
-- =====================================================
CREATE TABLE IF NOT EXISTS `konsultasi_gejala` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `konsultasi_id` INT NOT NULL,
    `gejala_id` INT NOT NULL,
    FOREIGN KEY (`konsultasi_id`) REFERENCES `konsultasi`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`gejala_id`) REFERENCES `gejala`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

-- =====================================================
-- DATA AWAL: PENGGUNA
-- =====================================================
INSERT INTO `users` (`nama`, `username`, `password`, `role`) VALUES
('Administrator', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Drg. Hj. Rini Sutarti', 'drgrini', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');
-- Password default: password

-- =====================================================
-- DATA AWAL: PENYAKIT GIGI
-- =====================================================
INSERT INTO `penyakit` (`kode`, `nama`, `deskripsi`, `solusi`) VALUES
('P001', 'Karies Gigi (Gigi Berlubang)', 
 'Karies gigi adalah kerusakan pada jaringan keras gigi yang disebabkan oleh bakteri. Bakteri dalam plak gigi menghasilkan asam yang melarutkan mineral pada email gigi, menyebabkan terjadinya lubang.',
 'Penanganan dilakukan dengan cara penambalan gigi (filling) menggunakan bahan komposit atau amalgam. Untuk karies yang sudah parah, mungkin diperlukan perawatan saluran akar atau pencabutan gigi. Pencegahan dengan menyikat gigi secara teratur dan mengurangi konsumsi makanan manis.'),

('P002', 'Pulpitis (Radang Pulpa Gigi)',
 'Pulpitis adalah peradangan pada pulpa gigi (bagian dalam gigi yang berisi pembuluh darah dan saraf). Kondisi ini sering disebabkan oleh karies yang tidak diobati sehingga bakteri mencapai pulpa.',
 'Jika pulpitis masih reversibel, dilakukan perawatan dengan penambalan. Jika sudah ireversibel, diperlukan perawatan saluran akar (root canal treatment/RCT). Hindari makanan/minuman panas, dingin, manis, dan asam.'),

('P003', 'Periodontitis (Radang Jaringan Penyangga Gigi)',
 'Periodontitis adalah infeksi serius yang merusak jaringan lunak dan tulang yang menopang gigi. Disebabkan oleh akumulasi plak dan kalkulus yang tidak dibersihkan, menyebabkan gusi meradang dan tulang alveolar menyusut.',
 'Perawatan meliputi scaling dan root planing (pembersihan karang gigi mendalam), kuretase, dan dalam kasus berat memerlukan operasi periodontal. Pasien dianjurkan menjaga kebersihan mulut dengan menyikat gigi dan menggunakan benang gigi (flossing).'),

('P004', 'Gingivitis (Radang Gusi)',
 'Gingivitis adalah peradangan pada gusi yang umumnya disebabkan oleh penumpukan plak bakteri di sepanjang garis gusi. Merupakan bentuk awal penyakit periodontal dan dapat disembuhkan jika ditangani dengan baik.',
 'Perawatan utama adalah scaling (pembersihan karang gigi) oleh dokter gigi. Di rumah, pasien harus rajin menyikat gigi minimal 2x sehari, menggunakan benang gigi, dan berkumur dengan obat kumur antiseptik.'),

('P005', 'Abses Periapikal (Abses Gigi)',
 'Abses periapikal adalah kantong nanah yang terbentuk di ujung akar gigi akibat infeksi bakteri. Biasanya merupakan komplikasi dari karies yang tidak diobati atau cedera pada gigi.',
 'Penanganan meliputi drainase abses, pemberian antibiotik, perawatan saluran akar, atau pencabutan gigi jika tidak bisa diselamatkan. Segera periksakan ke dokter gigi, jangan menunda karena infeksi bisa menyebar.'),

('P006', 'Gigi Sensitif (Dentin Hipersensitif)',
 'Gigi sensitif terjadi ketika lapisan dentin terbuka akibat email gigi yang terkikis atau resesi gusi. Dentin yang terbuka memungkinkan rangsangan (panas, dingin, manis, asam) langsung mencapai saraf gigi.',
 'Gunakan pasta gigi khusus sensitif yang mengandung kalium nitrat atau fluoride. Hindari makanan/minuman yang sangat panas, dingin, manis, atau asam. Dokter gigi dapat mengaplikasikan fluoride varnish atau bonding agent untuk menutupi dentin.'),

('P007', 'Stomatitis Aftosa (Sariawan)',
 'Stomatitis aftosa adalah luka kecil berbentuk bulat atau oval dengan tepi merah dan bagian tengah berwarna putih atau kuning yang timbul di dalam mulut. Penyebab pasti belum diketahui, namun dikaitkan dengan stres, defisiensi nutrisi, dan sistem imun.',
 'Gunakan obat kumur antiseptik atau kortikosteroid topikal untuk mengurangi peradangan. Hindari makanan pedas, asam, dan keras. Perbanyak konsumsi vitamin B12, zinc, dan asam folat. Umumnya sariawan sembuh sendiri dalam 1-2 minggu.'),

('P008', 'Fraktur Gigi (Gigi Retak/Patah)',
 'Fraktur gigi adalah kondisi di mana gigi mengalami retakan atau patah akibat trauma, menggigit benda keras, atau karena gigi yang sudah melemah akibat karies atau tambalan besar.',
 'Penanganan tergantung tingkat keparahan. Retak ringan dapat diobati dengan bonding atau mahkota gigi. Fraktur yang mencapai pulpa memerlukan perawatan saluran akar. Fraktur vertikal yang parah mungkin memerlukan pencabutan.');

-- =====================================================
-- DATA AWAL: GEJALA
-- =====================================================
INSERT INTO `gejala` (`kode`, `nama`) VALUES
('G001', 'Nyeri atau sakit gigi yang spontan (tanpa rangsangan)'),
('G002', 'Nyeri saat menggigit atau mengunyah makanan'),
('G003', 'Gigi terlihat berlubang atau berwarna kehitaman'),
('G004', 'Sensitivitas pada makanan/minuman panas'),
('G005', 'Sensitivitas pada makanan/minuman dingin'),
('G006', 'Sensitivitas pada makanan/minuman manis atau asam'),
('G007', 'Gusi berdarah saat menyikat gigi atau disentuh'),
('G008', 'Gusi bengkak, merah, dan terasa lunak'),
('G009', 'Gigi terasa goyang'),
('G010', 'Bau mulut (halitosis) yang tidak hilang'),
('G011', 'Terbentuknya kantong (poket) antara gigi dan gusi'),
('G012', 'Pembengkakan wajah atau rahang'),
('G013', 'Demam dan merasa tidak enak badan'),
('G014', 'Muncul benjolan berisi nanah (abses) di gusi'),
('G015', 'Nyeri berdenyut yang parah dan terus-menerus'),
('G016', 'Luka kecil berbentuk bulat di dalam mulut (pipi/lidah/bibir)'),
('G017', 'Tepi luka berwarna merah dengan tengah putih/kuning'),
('G018', 'Terasa perih saat makan atau berbicara'),
('G019', 'Gigi terlihat retak atau patah'),
('G020', 'Nyeri tiba-tiba saat menggigit benda keras'),
('G021', 'Karang gigi (kalkulus) menumpuk di sekitar gigi'),
('G022', 'Gusi mulai menyusut (resesi gusi)'),
('G023', 'Terdapat plak atau bercak putih di permukaan gigi'),
('G024', 'Nyeri menjalar ke kepala, telinga, atau rahang'),
('G025', 'Gigi terasa nyeri hanya saat ada rangsangan (bukan spontan)');

-- =====================================================
-- DATA AWAL: ATURAN (BASIS PENGETAHUAN)
-- IF [Gejala] THEN [Penyakit]
-- =====================================================

-- ATURAN KARIES GIGI (P001)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(1, 3),  -- G003: Gigi berlubang/kehitaman
(1, 5),  -- G005: Sensitif dingin
(1, 6),  -- G006: Sensitif manis/asam
(1, 23), -- G023: Plak/bercak putih
(1, 25); -- G025: Nyeri hanya saat rangsangan

-- ATURAN PULPITIS (P002)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(2, 1),  -- G001: Nyeri spontan
(2, 4),  -- G004: Sensitif panas
(2, 5),  -- G005: Sensitif dingin
(2, 15), -- G015: Nyeri berdenyut parah
(2, 24); -- G024: Nyeri menjalar

-- ATURAN PERIODONTITIS (P003)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(3, 7),  -- G007: Gusi berdarah
(3, 8),  -- G008: Gusi bengkak
(3, 9),  -- G009: Gigi goyang
(3, 10), -- G010: Bau mulut
(3, 11), -- G011: Terbentuk poket gusi
(3, 21), -- G021: Karang gigi menumpuk
(3, 22); -- G022: Resesi gusi

-- ATURAN GINGIVITIS (P004)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(4, 7),  -- G007: Gusi berdarah
(4, 8),  -- G008: Gusi bengkak
(4, 10), -- G010: Bau mulut
(4, 21); -- G021: Karang gigi menumpuk

-- ATURAN ABSES PERIAPIKAL (P005)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(5, 1),  -- G001: Nyeri spontan
(5, 2),  -- G002: Nyeri saat menggigit
(5, 12), -- G012: Pembengkakan wajah
(5, 13), -- G013: Demam
(5, 14), -- G014: Benjolan nanah di gusi
(5, 15); -- G015: Nyeri berdenyut parah

-- ATURAN GIGI SENSITIF (P006)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(6, 4),  -- G004: Sensitif panas
(6, 5),  -- G005: Sensitif dingin
(6, 6),  -- G006: Sensitif manis/asam
(6, 22), -- G022: Resesi gusi
(6, 25); -- G025: Nyeri hanya saat rangsangan

-- ATURAN STOMATITIS AFTOSA (P007)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(7, 16), -- G016: Luka kecil di dalam mulut
(7, 17), -- G017: Tepi merah tengah putih/kuning
(7, 18); -- G018: Perih saat makan/bicara

-- ATURAN FRAKTUR GIGI (P008)
INSERT INTO `aturan` (`penyakit_id`, `gejala_id`) VALUES
(8, 2),  -- G002: Nyeri saat menggigit
(8, 4),  -- G004: Sensitif panas
(8, 5),  -- G005: Sensitif dingin
(8, 19), -- G019: Gigi terlihat retak/patah
(8, 20); -- G020: Nyeri tiba-tiba saat menggigit keras
