<?php // Patient booking request: name, age, gender and reason. Staff then choose the doctor, date and time.
require 'config.php'; require 'layout.php'; require_login('patient');
$pid=(int)user()['patient_id'];
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  $name=trim($_POST['name']??'');$age=$_POST['age']??'';$gender=$_POST['gender']??'';$reason=trim($_POST['reason']??'');
  if($name===''||$age===''||(int)$age<0||(int)$age>120||!in_array($gender,['Female','Male','Other'])||$reason===''){
    flash('Please fill in your name, age, gender and reason for visit.');header('Location: request.php');exit;}
  q('UPDATE patients SET name=?,age=?,gender=? WHERE id=?',[$name,(int)$age,$gender,$pid]);
  q('INSERT INTO appointments(patient_id,reason,status) VALUES(?,?,"Pending")',[$pid,$reason]);
  flash('Request sent. Clinic staff will confirm your appointment and choose the date and time based on the doctors on duty. Check My appointments for the status.');
  header('Location: my.php');exit;}
$me=q('SELECT * FROM patients WHERE id=?',[$pid])->fetch();
head('Book appointment','request.php');?><h2>Book an appointment</h2>
<form class="panel" method="post"><?=csrf_field()?>
<p class="sub">Tell us about yourself and why you are visiting. Clinic staff will confirm your appointment and choose the date and time based on the doctors on duty.</p>
<div class="grid"><div><label for="n">Patient name</label><input id="n" name="name" value="<?=e($me['name']??'')?>" autocomplete="name" required></div>
<div><label for="a">Age</label><input id="a" name="age" type="number" min="0" max="120" value="<?=$me['age']?e($me['age']):''?>" required></div>
<div><label for="g">Gender</label><select id="g" name="gender" required><option value="">Select…</option><?php foreach(['Female','Male','Other'] as $g):?><option <?=($me['gender']??'')===$g?'selected':''?>><?=$g?></option><?php endforeach;?></select></div></div>
<label for="r">Reason for visit</label><textarea id="r" name="reason" rows="3" maxlength="255" required></textarea>
<p><button class="b">Send request</button></p></form><?php foot();
