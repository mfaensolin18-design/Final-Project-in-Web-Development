<?php require 'config.php'; require 'layout.php';
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  $u=q('SELECT * FROM users WHERE username=?',[trim($_POST['username']??'')])->fetch();
  if($u&&password_verify($_POST['password']??'',$u['password_hash'])){session_regenerate_id(true);
    $_SESSION['user']=['id'=>$u['id'],'name'=>$u['name'],'role'=>$u['role'],'doctor_id'=>$u['doctor_id'],'patient_id'=>$u['patient_id']];header('Location: '.($u['role']==='patient'?'duty.php':'index.php'));exit;}
  $err='Wrong username or password. Check both and try again.';}
head('Log in','login.php');?>
<div class="panel" style="max-width:380px;margin:30px auto"><h2>Log in</h2><?php if($err):?><div class="msg err" role="alert"><?=e($err)?></div><?php endif;?>
<form method="post"><?=csrf_field()?><label for="u">Username</label><input id="u" name="username" autocomplete="username" required>
<label for="p">Password</label><input id="p" name="password" type="password" autocomplete="current-password" required><p><button class="b">Log in</button></p></form>
<p class="sub">Demo accounts: staff / staff123 and drsantos / doctor123</p><p class="sub">New patient? <a href="register.php">Create an account</a></p></div><?php foot();
