<?php require 'config.php'; require 'layout.php';
$err='';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  $name=trim($_POST['name']??'');$un=trim($_POST['username']??'');$pw=$_POST['password']??'';
  if($name===''||$un==='')$err='Enter your name and a username.';
  elseif(strlen($pw)<6)$err='Password must have at least 6 characters.';
  elseif($pw!==($_POST['password2']??''))$err='Passwords do not match.';
  elseif(q('SELECT id FROM users WHERE username=?',[$un])->fetch())$err='That username is taken. Choose another.';
  else{q('INSERT INTO patients(name) VALUES(?)',[$name]);$pid=db()->lastInsertId();
    q('INSERT INTO users(username,password_hash,role,name,patient_id) VALUES(?,?,?,?,?)',[$un,password_hash($pw,PASSWORD_DEFAULT),'patient',$name,$pid]);
    session_regenerate_id(true);$_SESSION['user']=['id'=>db()->lastInsertId(),'name'=>$name,'role'=>'patient','doctor_id'=>null,'patient_id'=>$pid];
    flash('Account created. You can now check who is on duty and book an appointment.');header('Location: duty.php');exit;}}
head('Create account','register.php');?>
<div class="panel" style="max-width:380px;margin:30px auto"><h2>Create a patient account</h2><?php if($err):?><div class="msg err" role="alert"><?=e($err)?></div><?php endif;?>
<form method="post"><?=csrf_field()?><label for="n">Full name</label><input id="n" name="name" autocomplete="name" value="<?=e($_POST['name']??'')?>" required>
<label for="u">Username</label><input id="u" name="username" autocomplete="username" value="<?=e($_POST['username']??'')?>" required>
<label for="p">Password (at least 6 characters)</label><input id="p" name="password" type="password" autocomplete="new-password" required>
<label for="p2">Confirm password</label><input id="p2" name="password2" type="password" autocomplete="new-password" required>
<p><button class="b">Create account</button></p></form><p class="sub">Already registered? <a href="login.php">Log in</a></p></div><?php foot();
