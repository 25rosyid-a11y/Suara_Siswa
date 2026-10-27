<?php
require_once 'functions.php';
$stats = $pdo->query("SELECT COUNT(*) total, SUM(status='selesai') done FROM aspirations WHERE flagged=0 AND status<>'ditolak'")->fetch();
$categories = ['Fasilitas Sekolah','Kegiatan Ekstrakurikuler','Akademik','Kedisiplinan','Masukan Umum'];
$searchInput = $_GET['q'] ?? '';
$search = is_string($searchInput) ? trim(mb_substr($searchInput, 0, 100)) : '';
$category = $_GET['kategori'] ?? '';
if (!in_array($category, $categories, true)) $category = '';
$sort = $_GET['urut'] ?? 'terbaru';
if (!in_array($sort, ['terbaru','dukungan'], true)) $sort = 'terbaru';
$page = filter_input(INPUT_GET, 'halaman', FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) ?: 1;
$perPage = 8;
$conditions = ["a.is_public=1", "a.flagged=0", "a.status<>'ditolak'"];
$params = [];
if ($search !== '') {
  $conditions[] = '(a.title LIKE ? OR a.body LIKE ?)';
  $params[] = '%'.$search.'%';
  $params[] = '%'.$search.'%';
}
if ($category !== '') {
  $conditions[] = 'a.category=?';
  $params[] = $category;
}
$where = implode(' AND ', $conditions);
$countStmt = $pdo->prepare("SELECT COUNT(*) FROM aspirations a WHERE $where");
$countStmt->execute($params);
$resultCount = (int)$countStmt->fetchColumn();
$pageCount = max(1, (int)ceil($resultCount / $perPage));
$page = min($page, $pageCount);
$offset = ($page - 1) * $perPage;
$order = $sort === 'dukungan' ? 'votes DESC, a.created_at DESC' : 'a.created_at DESC';
$listStmt = $pdo->prepare("SELECT a.*, (SELECT COUNT(*) FROM votes v WHERE v.aspiration_id=a.id) votes FROM aspirations a WHERE $where ORDER BY $order LIMIT $perPage OFFSET $offset");
$listStmt->execute($params);
$latest = $listStmt->fetchAll();
page_header('Beranda','home');
?>
<section class="hero">
  <div class="hero-copy">
    <span class="eyebrow"><span class="pulse"></span> RUANG ASPIRASI SEKOLAH</span>
    <h1>Suaramu berarti.<br><span>Perubahan dimulai di sini.</span></h1>
    <p>Sampaikan ide, masukan, atau masalah di sekolah dengan mudah. Kami bantu aspirasi sampai ke pihak yang tepat.</p>
    <div class="hero-actions"><a class="btn btn-primary" href="kirim.php">Kirim Aspirasi <span>↗</span></a><a class="btn btn-light" href="lacak.php">Lacak Laporan</a></div>
    <div class="hero-note"><span class="avatar-stack"><i>✓</i><i>♡</i><i>✦</i></span><span>Terbuka, santun, dan berorientasi solusi</span></div>
  </div>
  <div class="hero-art" aria-label="Ilustrasi pesan aspirasi">
    <div class="art-orbit orbit-one"></div><div class="art-orbit orbit-two"></div>
    <div class="art-card main-art"><div class="art-icon">✉</div><div class="art-lines"><b>Aspirasi kamu</b><span>Didengar dan ditindaklanjuti</span></div><span class="check-bubble">✓</span></div>
    <div class="float-card float-top"><span class="float-icon mint">↗</span><span><b><?= (int)($stats['total'] ?? 0) ?></b><small>Aspirasi masuk</small></span></div>
    <div class="float-card float-bottom"><span class="float-icon orange">✓</span><span><b><?= (int)($stats['done'] ?? 0) ?></b><small>Sudah diselesaikan</small></span></div>
  </div>
</section>
<section class="trust-strip"><div><span class="trust-icon">◉</span><span><b>Bisa anonim</b><small>Identitas tidak ditampilkan</small></span></div><div><span class="trust-icon">↻</span><span><b>Status transparan</b><small>Pantau progres laporan</small></span></div><div><span class="trust-icon">♧</span><span><b>Ruang yang santun</b><small>Fokus pada solusi</small></span></div></section>
<section class="section">
  <div class="section-heading"><div><span class="eyebrow">MULAI DARI SINI</span><h2>Suarakan yang perlu diperbaiki</h2><p>Hanya perlu beberapa langkah untuk menyampaikan aspirasi.</p></div><a class="text-link" href="kirim.php">Buat aspirasi <span>→</span></a></div>
  <div class="category-grid">
    <a class="category-card cat-blue" href="kirim.php?kategori=Fasilitas%20Sekolah"><span class="category-icon">⌂</span><b>Fasilitas Sekolah</b><small>Ruang kelas, toilet, internet, dan lainnya</small><span class="card-arrow">↗</span></a>
    <a class="category-card cat-mint" href="kirim.php?kategori=Kegiatan%20Ekstrakurikuler"><span class="category-icon">✦</span><b>Kegiatan & Ekskul</b><small>Ide acara, klub, dan pengembangan bakat</small><span class="card-arrow">↗</span></a>
    <a class="category-card cat-orange" href="kirim.php?kategori=Akademik"><span class="category-icon">▤</span><b>Akademik</b><small>Belajar, jadwal, dan dukungan pembelajaran</small><span class="card-arrow">↗</span></a>
    <a class="category-card cat-purple" href="kirim.php?kategori=Masukan%20Umum"><span class="category-icon">♡</span><b>Masukan Umum</b><small>Ide untuk sekolah yang lebih baik</small><span class="card-arrow">↗</span></a>
  </div>
</section>
<section class="section">
  <div class="section-heading"><div><span class="eyebrow">ASPIRASI TERBARU</span><h2>Suara yang sedang diperjuangkan</h2><p>Hanya aspirasi yang telah disetujui pengurus yang tampil di sini.</p></div><a class="text-link" href="lacak.php">Lacak milikmu →</a></div>
  <form class="aspiration-filters" method="get" action="index.php">
    <label class="search-field"><span class="sr-only">Cari aspirasi</span><span aria-hidden="true">⌕</span><input type="search" name="q" value="<?= e($search) ?>" maxlength="100" placeholder="Cari judul atau isi aspirasi..."></label>
    <label><span class="sr-only">Filter kategori</span><select name="kategori"><option value="">Semua kategori</option><?php foreach($categories as $item): ?><option value="<?= e($item) ?>" <?= $category===$item?'selected':'' ?>><?= e($item) ?></option><?php endforeach; ?></select></label>
    <label><span class="sr-only">Urutkan aspirasi</span><select name="urut"><option value="terbaru" <?= $sort==='terbaru'?'selected':'' ?>>Terbaru</option><option value="dukungan" <?= $sort==='dukungan'?'selected':'' ?>>Dukungan terbanyak</option></select></label>
    <button class="btn btn-primary" type="submit">Terapkan</button>
    <?php if($search!==''||$category!==''||$sort!=='terbaru'): ?><a class="btn btn-outline" href="index.php">Reset</a><?php endif; ?>
  </form>
  <p class="filter-summary" aria-live="polite">Menampilkan <?= $resultCount ? $offset+1 : 0 ?>–<?= min($offset+$perPage,$resultCount) ?> dari <?= $resultCount ?> aspirasi</p>
  <?php if (!$latest): ?><div class="empty-state"><span>✦</span><h3><?= $search!==''||$category!==''?'Aspirasi tidak ditemukan':'Belum ada aspirasi publik' ?></h3><p><?= $search!==''||$category!==''?'Coba kata kunci atau kategori lain.':'Aspirasi yang disetujui pengurus akan muncul di sini.' ?></p><?php if($search===''&&$category===''): ?><a class="btn btn-primary" href="kirim.php">Jadi yang pertama</a><?php else: ?><a class="btn btn-outline" href="index.php">Hapus filter</a><?php endif; ?></div><?php else: ?>
  <div class="aspiration-grid"><?php foreach($latest as $a): ?><article class="asp-card"><div class="asp-meta"><span class="tag"><?= e($a['category']) ?></span><span class="status <?= e(status_class($a['status'])) ?>"><?= e(status_label($a['status'])) ?></span></div><h3><?= e($a['title']) ?></h3><p><?= e(mb_strimwidth($a['body'],0,145,'…')) ?></p><div class="asp-foot"><span>♡ <?= (int)$a['votes'] ?> dukungan</span><span><?= date('d M Y',strtotime($a['created_at'])) ?></span></div><form action="vote.php" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><button class="btn btn-outline btn-small" type="submit">♡ Dukung aspirasi</button></form></article><?php endforeach; ?></div>
  <?php if($pageCount>1): ?><nav class="pagination" aria-label="Halaman aspirasi">
    <?php if($page>1): ?><a class="btn btn-outline btn-small" href="?<?= e(http_build_query(['q'=>$search,'kategori'=>$category,'urut'=>$sort,'halaman'=>$page-1])) ?>">← Sebelumnya</a><?php endif; ?>
    <span>Halaman <?= $page ?> dari <?= $pageCount ?></span>
    <?php if($page<$pageCount): ?><a class="btn btn-outline btn-small" href="?<?= e(http_build_query(['q'=>$search,'kategori'=>$category,'urut'=>$sort,'halaman'=>$page+1])) ?>">Berikutnya →</a><?php endif; ?>
  </nav><?php endif; ?>
  <?php endif; ?>
</section>
<section class="guide-banner"><div class="guide-mark">i</div><div><h3>Jaga ruang ini tetap nyaman</h3><p>Gunakan bahasa yang sopan, ceritakan masalah dengan jelas, dan hindari menyebarkan data pribadi atau menuduh seseorang. Aspirasi akan ditinjau sebelum dipublikasikan.</p></div><a class="btn btn-light" href="halaman.php?page=profil">Panduan lengkap</a></section>
<?php page_footer(); ?>
