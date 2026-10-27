<?php
require_once 'functions.php';
if(is_admin()) redirect('admin.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $username=trim($_POST['username']??'');$password=$_POST['password']??'';
 $stmt=$pdo->prepare("SELECT * FROM admins WHERE username=? LIMIT 1");$stmt->execute([$username]);$admin=$stmt->fetch();
 if($admin && password_verify($password,$admin['password_hash'])){
  session_regenerate_id(true);$_SESSION['admin_id']=$admin['id'];$_SESSION['admin_name']=$admin['name'];$_SESSION['admin_role']=$admin['role'];redirect('admin.php');
 } else $error='Username atau kata sandi tidak sesuai.';
}
page_header('Login Pengurus');
?>
<section class="login-wrap"><div class="login-side"><span class="brand-mark large">S</span><span class="eyebrow">AREA PENGELOLAAN</span><h1>Kelola aspirasi.<br><span>Dorong perubahan.</span></h1><p>Dashboard ini khusus untuk pengurus yang berwenang.</p></div><form class="panel login-panel" method="post"><h2>Masuk pengurus</h2><p class="muted">Gunakan akun yang telah dibuat oleh administrator.</p><?php if($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?><input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>"><label for="username">Username</label><input id="username" name="username" autocomplete="username" required><label for="password">Kata sandi</label><input id="password" name="password" type="password" autocomplete="current-password" required><button class="btn btn-primary btn-wide" type="submit">Masuk ke dashboard →</button><a class="text-link center-link" href="index.php">← Kembali ke halaman publik</a></form></section>
<?php page_footer(); ?>
