<?php
require_once 'functions.php';require_admin();
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);$action=$_POST['action']??'';
 if($id){
  if($action==='update'){
   $status=$_POST['status']??'diterima';$response=trim($_POST['response']??'');$public=isset($_POST['is_public'])?1:0;
   $valid=['diterima','ditinjau','tindak_lanjut','selesai','ditolak'];
   if(in_array($status,$valid,true)){
    $stmt=$pdo->prepare("UPDATE aspirations SET status=?,admin_response=?,is_public=?,flagged=0 WHERE id=?");$stmt->execute([$status,$response,$public,$id]);
   }
  } elseif($action==='delete') {$stmt=$pdo->prepare("DELETE FROM aspirations WHERE id=?");$stmt->execute([$id]);}
 }
 redirect('admin.php?updated=1');
}
$filter=$_GET['status']??'semua';$allowed=['semua','diterima','ditinjau','tindak_lanjut','selesai','ditolak'];
if(!in_array($filter,$allowed,true))$filter='semua';
$where=$filter==='semua'?'':'WHERE status=?';
$stmt=$pdo->prepare("SELECT * FROM aspirations $where ORDER BY created_at DESC LIMIT 100");$stmt->execute($filter==='semua'?[]:[$filter]);$rows=$stmt->fetchAll();
$stats=$pdo->query("SELECT COUNT(*) total,SUM(status='diterima') baru,SUM(status='tindak_lanjut') tindak,SUM(status='selesai') selesai,SUM(flagged=1) flagged FROM aspirations")->fetch();
page_header('Dashboard Pengurus');
?>
<section class="dashboard-head"><div><span class="eyebrow">DASHBOARD PENGELOLAAN</span><h1>Halo, <?= e($_SESSION['admin_name']??'Pengurus') ?> 👋</h1><p>Kelola aspirasi dengan objektif, santun, dan bertanggung jawab.</p></div><a class="btn btn-outline" href="logout.php">Keluar ↗</a></section>
<?php if(isset($_GET['updated'])): ?><div class="alert alert-success">Perubahan tersimpan.</div><?php endif; ?>
<div class="stats-grid"><div class="stat-card"><span>Total aspirasi</span><strong><?= (int)$stats['total'] ?></strong><small>Semua laporan</small></div><div class="stat-card"><span>Perlu ditinjau</span><strong><?= (int)$stats['baru'] ?></strong><small>Status diterima</small></div><div class="stat-card"><span>Tindak lanjut</span><strong><?= (int)$stats['tindak'] ?></strong><small>Sedang diproses</small></div><div class="stat-card"><span>Selesai</span><strong><?= (int)$stats['selesai'] ?></strong><small>Telah ditangani</small></div></div>
<section class="section admin-list"><div class="section-heading"><div><h2>Daftar aspirasi</h2><p>Periksa konten sebelum menjadikannya publik.</p></div><form method="get"><select name="status" aria-label="Filter status" onchange="this.form.submit()"><?php foreach($allowed as $s): ?><option value="<?= e($s) ?>" <?= $filter===$s?'selected':'' ?>><?= $s==='semua'?'Semua status':e(status_label($s)) ?></option><?php endforeach; ?></select></form></div>
<?php if(!$rows): ?><div class="empty-state"><h3>Belum ada laporan</h3><p>Aspirasi baru akan tampil di sini.</p></div><?php endif; ?>
<?php foreach($rows as $a): ?><article class="panel admin-asp"><div class="admin-asp-head"><div><div class="asp-meta"><span class="tag"><?= e($a['category']) ?></span><span class="status <?= e(status_class($a['status'])) ?>"><?= e(status_label($a['status'])) ?></span><?php if($a['flagged']): ?><span class="status red">Perlu moderasi</span><?php endif; ?></div><h3><?= e($a['title']) ?></h3><p><?= nl2br(e($a['body'])) ?></p><small class="muted">Kode <?= e($a['tracking_code']) ?> · <?= $a['is_anonymous']?'Anonim':'Identitas: '.e($a['student_name']) ?> · <?= date('d M Y H:i',strtotime($a['created_at'])) ?></small></div></div>
<?php if($a['attachment']): ?><p><a class="text-link" target="_blank" rel="noopener" href="uploads/<?= rawurlencode($a['attachment']) ?>">Lihat lampiran ↗</a></p><?php endif; ?>
<form class="admin-edit" method="post"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="action" value="update"><div><label>Status</label><select name="status"><?php foreach(['diterima','ditinjau','tindak_lanjut','selesai','ditolak'] as $s): ?><option value="<?= e($s) ?>" <?= $a['status']===$s?'selected':'' ?>><?= e(status_label($s)) ?></option><?php endforeach; ?></select></div><div class="response-field"><label>Catatan/tanggapan untuk pelapor</label><textarea name="response" rows="2" placeholder="Tulis perkembangan atau tanggapan..."><?= e($a['admin_response']) ?></textarea></div><label class="check-row public-check"><input type="checkbox" name="is_public" value="1" <?= $a['is_public']?'checked':'' ?>> <span>Tampilkan di publik</span></label><button class="btn btn-primary btn-small" type="submit">Simpan perubahan</button></form>
<form method="post" class="delete-form" onsubmit="return confirm('Hapus aspirasi ini secara permanen?')"><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><input type="hidden" name="id" value="<?= (int)$a['id'] ?>"><input type="hidden" name="action" value="delete"><button class="text-danger" type="submit">Hapus aspirasi</button></form>
</article><?php endforeach; ?></section>
<?php page_footer(); ?>
