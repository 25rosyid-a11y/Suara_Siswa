# Suara Siswa — PHP & MySQL

Website aspirasi sekolah responsif dengan:
- Form aspirasi (anonim atau mencantumkan identitas)
- Kategori aspirasi, lampiran gambar/PDF, kode pelacakan
- Pelacakan status: Diterima → Ditinjau → Tindak Lanjut → Selesai
- Dukungan/upvote untuk aspirasi publik yang disetujui
- Login pengurus, dashboard statistik, moderasi, dan pembaruan status
- Halaman Profil, Berita, Agenda, Program Kerja, Galeri
- Toggle mode terang/gelap
- Password admin disimpan sebagai hash (`password_hash`)

## Persyaratan
- PHP 8.1+ dengan PDO MySQL, fileinfo, dan session aktif
- MySQL/MariaDB
- Apache (misalnya XAMPP/FlyEnv)

## Instalasi lokal
1. Ekstrak folder `suara_siswa` ke direktori web server, misalnya `htdocs/suara_siswa`.
2. Buat database dengan mengimpor `database.sql` melalui phpMyAdmin atau MySQL CLI.
3. Sesuaikan kredensial database di `config.php`.
4. Buka `http://localhost/suara_siswa/setup_admin.php`.
5. Buat akun pengurus pertama. Setelah berhasil, **hapus `setup_admin.php` dari server**.
6. Buka `http://localhost/suara_siswa/`.

## Catatan keamanan sebelum dipakai sungguhan
- Ganti `APP_BASE_URL` bila proyek dipasang pada subfolder berbeda.
- Aktifkan HTTPS saat dipublikasikan.
- Pastikan folder `uploads/` tidak bisa mengeksekusi file PHP; konfigurasi Apache `.htaccess` disertakan.
- Buat backup database berkala dan batasi akun admin.
- Fitur filter kata tidak pantas hanya membantu moderasi, bukan jaminan sempurna. Admin tetap perlu meninjau laporan.
- Anonim berarti nama tidak ditampilkan dan tidak diminta pada formulir. Jangan mengklaim anonimitas teknis absolut jika server/log masih menyimpan metadata.
- Lampiran dibatasi ke JPG, PNG, WEBP, dan PDF; maksimal 3 MB.
- Berita/agenda/program/galeri pada paket awal adalah konten contoh yang ditulis di `content.php`; pengelolaan konten melalui dashboard adalah pengembangan berikutnya.

## GitHub Pages (versi statis)

Folder `docs/` berisi versi statis yang dapat diterbitkan melalui GitHub Pages. File PHP asli tetap tersedia untuk hosting PHP/MySQL.

1. Buat repository GitHub dan unggah seluruh isi folder proyek ini.
2. Pastikan branch utama bernama `main` atau `master`.
3. Buka **Settings → Pages**, lalu pilih **GitHub Actions** sebagai sumber deployment.
4. Workflow `.github/workflows/pages.yml` akan menerbitkan folder `docs/` setelah perubahan didorong ke branch utama. Alamat situs muncul pada tab **Actions** atau **Settings → Pages**.

### Batasan versi statis

- Aspirasi, dukungan, dan kode pelacakan disimpan di `localStorage` browser, bukan pada server. Data tidak dibagikan ke siswa lain dan tidak berpindah ke perangkat/browser lain.
- Versi ini tidak menyediakan login/admin, moderasi bersama, lampiran, atau status yang diperbarui pengurus. Jangan gunakan penyimpanan browser sebagai tempat laporan sensitif.
- Untuk layanan sekolah yang dipakai bersama, deploy aplikasi PHP/MySQL pada hosting yang mendukung PHP dan gunakan GitHub untuk menyimpan kode sumber.
