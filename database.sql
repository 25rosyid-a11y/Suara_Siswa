CREATE DATABASE IF NOT EXISTS suara_siswa CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE suara_siswa;

CREATE TABLE IF NOT EXISTS admins (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 name VARCHAR(100) NOT NULL,
 username VARCHAR(60) NOT NULL UNIQUE,
 password_hash VARCHAR(255) NOT NULL,
 role ENUM('admin','osis') NOT NULL DEFAULT 'osis',
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS aspirations (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 tracking_code VARCHAR(20) NOT NULL UNIQUE,
 category ENUM('Fasilitas Sekolah','Kegiatan Ekstrakurikuler','Akademik','Kedisiplinan','Masukan Umum') NOT NULL,
 title VARCHAR(140) NOT NULL,
 body TEXT NOT NULL,
 is_anonymous TINYINT(1) NOT NULL DEFAULT 1,
 student_name VARCHAR(100) NULL,
 attachment VARCHAR(255) NULL,
 status ENUM('diterima','ditinjau','tindak_lanjut','selesai','ditolak') NOT NULL DEFAULT 'diterima',
 admin_response TEXT NULL,
 is_public TINYINT(1) NOT NULL DEFAULT 0,
 flagged TINYINT(1) NOT NULL DEFAULT 0,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 INDEX idx_status(status), INDEX idx_public(is_public), INDEX idx_created(created_at)
);

CREATE TABLE IF NOT EXISTS votes (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 aspiration_id BIGINT UNSIGNED NOT NULL,
 voter_token CHAR(64) NOT NULL,
 created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY unique_vote (aspiration_id, voter_token),
 CONSTRAINT fk_vote_aspiration FOREIGN KEY (aspiration_id) REFERENCES aspirations(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS site_content (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
 content_key VARCHAR(80) NOT NULL UNIQUE,
 title VARCHAR(180) NOT NULL,
 body TEXT NOT NULL,
 updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO site_content(content_key,title,body) VALUES
('profil','Tentang Suara Siswa','Suara Siswa adalah ruang komunikasi yang membantu siswa menyampaikan aspirasi secara santun dan konstruktif.'),
('berita','Berita Sekolah','Belum ada berita terbaru. Pengurus dapat menambahkan berita pada pengembangan berikutnya.'),
('agenda','Agenda OSIS','Agenda kegiatan OSIS akan diumumkan melalui halaman ini.'),
('program','Program Kerja OSIS','Program kerja disusun untuk mendukung lingkungan sekolah yang aman, nyaman, aktif, dan berprestasi.'),
('galeri','Galeri Kegiatan','Dokumentasi kegiatan OSIS akan ditampilkan di halaman ini.');
