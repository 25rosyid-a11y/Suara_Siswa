<?php
require_once 'functions.php';
$pages=[
 'profil'=>['Profil','Tentang Suara Siswa','Suara Siswa adalah ruang komunikasi yang membantu siswa menyampaikan aspirasi secara santun dan konstruktif. Tujuannya membangun budaya mendengar, berdiskusi, dan mencari solusi bersama.'],
 'berita'=>['Berita','Berita dan Informasi','Informasi kegiatan OSIS, pengumuman, serta kabar baik dari lingkungan sekolah akan ditampilkan di sini.'],
 'agenda'=>['Agenda','Agenda OSIS','Agenda kegiatan OSIS dan program sekolah akan diperbarui oleh pengurus. Periksa pengumuman resmi sekolah untuk informasi terbaru.'],
 'program'=>['Program Kerja','Program Kerja OSIS','Program kerja difokuskan pada lingkungan sekolah yang aman dan nyaman, peningkatan partisipasi siswa, kepedulian fasilitas, serta kegiatan positif dan kreatif.'],
 'galeri'=>['Galeri','Galeri Kegiatan','Dokumentasi kegiatan OSIS akan ditampilkan di halaman ini setelah dipilih dan diunggah oleh pengurus.']
];
$key=$_GET['page']??'profil';if(!isset($pages[$key]))$key='profil';
[$title,$heading,$body]=$pages[$key];
page_header($title,$key);
?>
<section class="content-hero"><span class="eyebrow">OSIS · <?= e(OSIS_PERIOD) ?></span><h1><?= e($heading) ?></h1><p><?= e($body) ?></p></section>
<section class="section content-columns"><article class="panel content-panel"><span class="category-icon">✦</span><h2>Terbuka untuk semua siswa</h2><p>Setiap masukan yang disampaikan dengan baik dapat menjadi langkah awal perbaikan. Sampaikan ide dengan jelas dan berikan informasi yang relevan.</p><a class="btn btn-primary" href="kirim.php">Sampaikan aspirasi →</a></article><article class="panel content-panel"><span class="category-icon">✓</span><h2>Proses yang bertanggung jawab</h2><p>Pengurus meninjau laporan, menentukan tindak lanjut, dan memberikan pembaruan status sesuai perkembangan yang ada.</p><a class="btn btn-outline" href="lacak.php">Lacak aspirasi</a></article></section>
<?php page_footer(); ?>
