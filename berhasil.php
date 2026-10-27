<?php
require_once 'functions.php';
$code=preg_replace('/[^A-Z0-9-]/','',strtoupper($_GET['kode']??''));
page_header('Aspirasi Terkirim');
?>
<section class="success-wrap"><div class="success-icon">✓</div><span class="eyebrow">TERKIRIM</span><h1>Terima kasih sudah bersuara!</h1><p>Aspirasi kamu sudah diterima. Simpan kode ini untuk memantau perkembangan laporan.</p><div class="tracking-code"><small>KODE PELACAKAN</small><strong><?= e($code) ?></strong><button type="button" class="btn btn-outline btn-small" data-copy="<?= e($code) ?>">Salin kode</button></div><a class="btn btn-primary" href="lacak.php?kode=<?= urlencode($code) ?>">Lacak status →</a><p class="fine-print">Simpan kode pelacakan di tempat yang aman. Kode ini diperlukan untuk melihat detail laporan.</p></section>
<?php page_footer(); ?>
