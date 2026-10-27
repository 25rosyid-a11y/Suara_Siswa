<?php
require_once __DIR__ . '/config.php';

function e(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function redirect(string $path): never {
    header('Location: ' . $path);
    exit;
}
function csrf_token(): string {
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    $sent = $_POST['csrf'] ?? '';
    if (!is_string($sent) || !hash_equals(csrf_token(), $sent)) {
        http_response_code(403);
        exit('Permintaan tidak valid. Muat ulang halaman lalu coba lagi.');
    }
}
function is_admin(): bool {
    return isset($_SESSION['admin_id']);
}
function require_admin(): void {
    if (!is_admin()) redirect('login.php');
}
function status_label(string $status): string {
    return match ($status) {
        'ditinjau' => 'Ditinjau',
        'tindak_lanjut' => 'Tindak Lanjut',
        'selesai' => 'Selesai',
        'ditolak' => 'Tidak Dipublikasikan',
        default => 'Diterima',
    };
}
function status_class(string $status): string {
    return match ($status) {
        'ditinjau' => 'blue',
        'tindak_lanjut' => 'orange',
        'selesai' => 'green',
        'ditolak' => 'red',
        default => 'gray',
    };
}
function flagged_words(string $text): bool {
    // Daftar contoh minimal. Sesuaikan dengan konteks sekolah; bukan sistem moderasi sempurna.
    $words = ['anjing', 'bangsat', 'kontol', 'memek', 'tolol'];
    $normalized = mb_strtolower($text);
    foreach ($words as $word) {
        if (preg_match('/(?<![\pL\pN])' . preg_quote($word, '/') . '(?![\pL\pN])/u', $normalized)) return true;
    }
    return false;
}
function page_header(string $title, string $active = ''): void { ?>
<!doctype html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="theme-color" content="#2563eb">
<title><?= e($title) ?> · <?= e(APP_NAME) ?></title>
<link rel="stylesheet" href="assets/style.css">
<script defer src="assets/app.js"></script>
</head>
<body>
<header class="topbar">
  <a class="brand" href="index.php"><span class="brand-mark">S</span><span>Suara<span class="brand-accent">Siswa</span><small><?= e(SCHOOL_NAME) ?></small></span></a>
    <button class="menu-toggle" aria-label="Buka navigasi" aria-expanded="false" aria-controls="site-nav" data-menu-toggle>☰</button>
    <nav class="nav" id="site-nav" data-nav>
    <a class="<?= $active==='home'?'active':'' ?>" href="index.php">Beranda</a>
    <a class="<?= $active==='profil'?'active':'' ?>" href="halaman.php?page=profil">Profil</a>
    <a class="<?= $active==='berita'?'active':'' ?>" href="halaman.php?page=berita">Berita</a>
    <a class="<?= $active==='agenda'?'active':'' ?>" href="halaman.php?page=agenda">Agenda</a>
    <a href="halaman.php?page=program">Program Kerja</a>
    <a href="halaman.php?page=galeri">Galeri</a>
    <a class="nav-admin" href="<?= is_admin() ? 'admin.php' : 'login.php' ?>"><?= is_admin() ? 'Dashboard' : 'Pengurus' ?></a>
    <button class="theme-toggle" type="button" data-theme-toggle aria-label="Ganti mode">◐</button>
  </nav>
</header>
<main class="container">
<?php }
function page_footer(): void { ?>
</main>
<footer class="footer"><div><strong>Suara Siswa</strong><p>Ruang aspirasi yang santun, aman, dan membangun.</p></div><span>© <?= date('Y') ?> · OSIS <?= e(OSIS_PERIOD) ?></span></footer>
</body></html>
<?php }
