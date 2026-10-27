<?php
require_once 'functions.php';
if($_SERVER['REQUEST_METHOD']!=='POST') redirect('index.php');
verify_csrf();
$id=filter_input(INPUT_POST,'id',FILTER_VALIDATE_INT);
if(!$id) redirect('index.php');
if(empty($_COOKIE['ss_voter'])) {
 $token=bin2hex(random_bytes(32));
 setcookie('ss_voter',$token,['expires'=>time()+31536000,'path'=>'/','secure'=>!empty($_SERVER['HTTPS']),'httponly'=>true,'samesite'=>'Lax']);
} else $token=preg_replace('/[^a-f0-9]/','',$_COOKIE['ss_voter']);
$hash=hash('sha256',$token);
try {
 $check=$pdo->prepare("SELECT id FROM aspirations WHERE id=? AND is_public=1 AND flagged=0 AND status<>'ditolak'");$check->execute([$id]);
 if($check->fetch()){$stmt=$pdo->prepare("INSERT IGNORE INTO votes(aspiration_id,voter_token) VALUES(?,?)");$stmt->execute([$id,$hash]);}
} catch(Throwable $e) {}
redirect('index.php');
