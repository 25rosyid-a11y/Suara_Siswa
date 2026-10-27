<?php
require_once 'functions.php';
$result=null; $error='';
$code=trim($_GET['kode']??'');
if($_SERVER['REQUEST_METHOD']==='POST'){verify_csrf();$code=trim($_POST['kode']??'');}
if($code!==''){
 $stmt=$pdo->prepare("SELECT * FROM aspirations WHERE tracking_code=?");$stmt->execute([$code]);$result=$stmt->fetch();
 if(!$result)$error='Kode tidak ditemukan. Periksa kembali kode pelacakan.';
}
page_header('Lacak Aspirasi');
?>
<section class="track-wrap"><span class="eyebrow">STATUS TRACKER</span><h1>Bagaimana perkembangan aspirasimu?</h1><p>Masukkan kode pelacakan yang kamu dapatkan setelah mengirim aspirasi.</p>
<form class="track-form" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label class="sr-only" for="kode">Kode pelacakan</label><input id="kode" name="kode" value="<?= e($code) ?>" placeholder="Contoh: SS-A1B2C3D4" required><button class="btn btn-primary" type="submit">Lacak status</button></form>
<?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<?php if($result): ?><article class="panel track-result"><div class="asp-meta"><span class="tag"><?= e($result['category']) ?></span><span class="status <?= e(status_class($result['status'])) ?>"><?= e(status_label($result['status'])) ?></span></div><h2><?= e($result['title']) ?></h2><p><?= nl2br(e($result['body'])) ?></p><div class="timeline"><?php $order=['diterima','ditinjau','tindak_lanjut','selesai'];$idx=array_search($result['status'],$order,true);foreach($order as $i=>$s): ?><div class="timeline-step <?= ($idx!==false && $i<=$idx)?'done':'' ?>"><span><?= ($idx!==false && $i<=$idx)?'✓':($i+1) ?></span><div><b><?= e(status_label($s)) ?></b><?php if($i===0): ?><small>Aspirasi masuk ke sistem</small><?php elseif($s===$result['status']): ?><small>Status terakhir diperbarui <?= date('d M Y, H:i',strtotime($result['updated_at'])) ?></small><?php endif; ?></div></div><?php endforeach; ?></div><?php if($result['admin_response']): ?><div class="response-box"><b>Catatan pengurus</b><p><?= nl2br(e($result['admin_response'])) ?></p></div><?php endif; ?><small class="muted">Kode: <?= e($result['tracking_code']) ?> · Dikirim <?= date('d M Y',strtotime($result['created_at'])) ?></small></article><?php endif; ?>
</section>
<?php page_footer(); ?>
