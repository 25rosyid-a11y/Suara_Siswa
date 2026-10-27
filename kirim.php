<?php
require_once 'functions.php';
$categories=['Fasilitas Sekolah','Kegiatan Ekstrakurikuler','Akademik','Kedisiplinan','Masukan Umum'];
$selected=$_GET['kategori']??'Masukan Umum';
if(!in_array($selected,$categories,true)) $selected='Masukan Umum';
$errors=[];
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $category=$_POST['category']??''; $title=trim($_POST['title']??''); $body=trim($_POST['body']??'');
 $anon=isset($_POST['anonymous']); $name=trim($_POST['student_name']??'');
 if(!in_array($category,$categories,true)) $errors[]='Pilih kategori yang valid.';
 if(mb_strlen($title)<5||mb_strlen($title)>140) $errors[]='Judul harus 5–140 karakter.';
 if(mb_strlen($body)<15||mb_strlen($body)>5000) $errors[]='Isi aspirasi harus 15–5.000 karakter.';
 if(!$anon && $name==='') $errors[]='Isi nama jika memilih mencantumkan identitas.';
 $flagged=flagged_words($title.' '.$body);
 $attachment=null;
 if(isset($_FILES['attachment']) && $_FILES['attachment']['error']!==UPLOAD_ERR_NO_FILE){
  $f=$_FILES['attachment'];
  if($f['error']!==UPLOAD_ERR_OK) $errors[]='Lampiran gagal diunggah.';
  elseif($f['size']>3*1024*1024) $errors[]='Ukuran lampiran maksimal 3 MB.';
  else {
   $finfo=new finfo(FILEINFO_MIME_TYPE); $mime=$finfo->file($f['tmp_name']);
   $allowed=['image/jpeg'=>'jpg','image/png'=>'png','image/webp'=>'webp','application/pdf'=>'pdf'];
   if(!isset($allowed[$mime])) $errors[]='Lampiran hanya boleh JPG, PNG, WEBP, atau PDF.';
   else {
    $dir=__DIR__.'/uploads'; if(!is_dir($dir)) mkdir($dir,0750,true);
    $filename=bin2hex(random_bytes(18)).'.'.$allowed[$mime];
    if(move_uploaded_file($f['tmp_name'],$dir.'/'.$filename)) $attachment=$filename; else $errors[]='Lampiran tidak dapat disimpan.';
   }
  }
 }
 if(!$errors){
  $code='SS-'.strtoupper(bin2hex(random_bytes(4)));
  $stmt=$pdo->prepare("INSERT INTO aspirations(tracking_code,category,title,body,is_anonymous,student_name,attachment,flagged,is_public) VALUES(?,?,?,?,?,?,?,?,0)");
  $stmt->execute([$code,$category,$title,$body,$anon?1:0,$anon?null:$name,$attachment,$flagged?1:0]);
  $_SESSION['last_tracking_code']=$code;
  redirect('berhasil.php?kode='.urlencode($code));
 }
 $selected=in_array($category,$categories,true)?$category:'Masukan Umum';
}
page_header('Kirim Aspirasi');
?>
<div class="form-layout"><div class="form-intro"><span class="eyebrow">FORMULIR ASPIRASI</span><h1>Ceritakan, kami siap mendengar.</h1><p>Jelaskan apa yang terjadi atau ide yang ingin kamu sampaikan. Pengurus akan meninjau laporanmu.</p><div class="privacy-note"><span>♧</span><div><b>Privasi adalah hal penting</b><p>Jika memilih anonim, nama tidak diminta dan tidak ditampilkan. Kode pelacakan tetap diberikan untuk memantau status.</p></div></div><div class="steps"><div><span>1</span><b>Tulis aspirasi</b></div><div><span>2</span><b>Simpan kode pelacakan</b></div><div><span>3</span><b>Pantau perkembangannya</b></div></div></div>
<form class="panel form-panel" method="post" enctype="multipart/form-data">
<input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">
<h2>Form aspirasi</h2><p class="muted">Bidang bertanda * wajib diisi.</p>
<?php foreach($errors as $err): ?><div class="alert alert-error"><?= e($err) ?></div><?php endforeach; ?>
<label for="category">Kategori *</label><select id="category" name="category" required><?php foreach($categories as $c): ?><option <?= $selected===$c?'selected':'' ?> value="<?= e($c) ?>"><?= e($c) ?></option><?php endforeach; ?></select>
<label for="title">Judul singkat *</label><input id="title" name="title" maxlength="140" minlength="5" required placeholder="Contoh: Perbaikan koneksi Wi-Fi di perpustakaan" value="<?= e($_POST['title']??'') ?>"><div class="field-meta"><span class="field-hint">Tulis judul yang jelas dan spesifik.</span><span class="char-count" data-count-for="title" aria-live="polite"></span></div>
<label for="body">Ceritakan aspirasi kamu *</label><textarea id="body" name="body" rows="6" minlength="15" maxlength="5000" required placeholder="Jelaskan situasi, lokasi jika relevan, dan solusi yang kamu harapkan..."><?= e($_POST['body']??'') ?></textarea><div class="field-meta"><span class="field-hint">Hindari menyertakan kata sandi, nomor telepon, atau data pribadi orang lain.</span><span class="char-count" data-count-for="body" aria-live="polite"></span></div>
<label class="upload-box" for="attachment"><span class="upload-icon">↑</span><span><b>Tambah lampiran (opsional)</b><small>JPG, PNG, WEBP, atau PDF · Maks. 3 MB</small><small class="file-name" data-file-name>Belum ada file dipilih</small></span><input id="attachment" type="file" name="attachment" accept=".jpg,.jpeg,.png,.webp,.pdf"></label>
<div class="identity-choice"><label class="check-row"><input type="checkbox" name="anonymous" value="1" checked data-anon-toggle> <span><b>Kirim secara anonim</b><small>Nama kamu tidak diminta dan tidak ditampilkan.</small></span></label><div data-name-field hidden><label for="student_name">Nama siswa *</label><input id="student_name" name="student_name" placeholder="Nama kamu" value="<?= e($_POST['student_name']??'') ?>"></div></div>
<label class="check-row consent"><input type="checkbox" required> <span>Saya menyampaikan aspirasi dengan sopan dan memahami bahwa laporan ditinjau pengurus.</span></label>
<button class="btn btn-primary btn-wide" type="submit">Kirim aspirasi <span>→</span></button><p class="fine-print">Aspirasi tidak otomatis dipublikasikan. Pengurus akan memeriksa konten sebelum menampilkannya.</p>
</form></div>
<?php page_footer(); ?>
