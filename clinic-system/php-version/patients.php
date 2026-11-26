<?php require 'config.php'; require 'layout.php'; require_login();$u=user();
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  if(isset($_POST['add_patient'])&&$u['role']==='staff'&&trim($_POST['name'])!==''){q('INSERT INTO patients(name,age,phone) VALUES(?,?,?)',[trim($_POST['name']),(int)$_POST['age'],trim($_POST['phone'])]);
    flash('Patient registered.');header('Location: patients.php?id='.db()->lastInsertId());exit;}
  if(isset($_POST['add_note'])&&$u['role']==='doctor'&&trim($_POST['note'])!==''){q('INSERT INTO visit_notes(patient_id,doctor_id,note_date,note) VALUES(?,?,CURDATE(),?)',[(int)$_POST['pid'],$u['doctor_id'],trim($_POST['note'])]);
    flash('Note saved.');header('Location: patients.php?id='.(int)$_POST['pid']);exit;}
  header('Location: patients.php');exit;}
$s=trim($_GET['q']??'');$list=q('SELECT * FROM patients WHERE name LIKE ? ORDER BY name',["%$s%"])->fetchAll();
$p=isset($_GET['id'])?q('SELECT * FROM patients WHERE id=?',[(int)$_GET['id']])->fetch():null;
$notes=$p?q('SELECT n.*,d.name dn FROM visit_notes n JOIN doctors d ON d.id=n.doctor_id WHERE patient_id=? ORDER BY note_date DESC,n.id DESC',[$p['id']])->fetchAll():[];
head('Patients and records','patients.php');?><h2>Patients and records</h2><div class="cols"><div>
<form class="panel" method="get"><label for="q">Search patients</label><input id="q" name="q" value="<?=e($s)?>" placeholder="Type a name">
<?php foreach($list as $x):?><div class="row"><div class="grow"><b><?=e($x['name'])?></b><div class="sub"><?=$x['age']?> yrs · <?=e($x['phone'])?></div></div><a class="g" href="?id=<?=$x['id']?>">Open record</a></div><?php endforeach;if(!$list)echo '<p class="sub">No patient found.</p>';?></form>
<?php if($u['role']==='staff'):?><form class="panel" method="post"><?=csrf_field()?><h3>Register new patient</h3><label for="n">Full name</label><input id="n" name="name" required><label for="a">Age</label><input id="a" type="number" name="age" min="0" max="120"><label for="ph">Phone</label><input id="ph" name="phone"><p><button class="b" name="add_patient" value="1">Add patient</button></p></form><?php endif;?></div>
<div class="panel rec"><?php if($p):?><div class="printonly"><b>Clinic One</b>: Patient record</div><button class="g noprint" style="float:right" onclick="window.print()">Print record</button>
<h3><?=e($p['name'])?></h3><div class="sub"><?=$p['age']?> yrs<?=$p['gender']?' · '.e($p['gender']):''?> · <?=e($p['phone'])?></div><h3 style="margin-top:16px">Visit notes</h3>
<?php foreach($notes as $n):?><div class="note"><div class="sub"><?=fmt_date($n['note_date'])?> · <?=e($n['dn'])?></div><?=nl2br(e($n['note']))?></div><?php endforeach;if(!$notes)echo '<p class="sub">No notes yet.</p>';?>
<?php if($u['role']==='doctor'):?><form method="post"><?=csrf_field()?><input type="hidden" name="pid" value="<?=$p['id']?>"><label for="nt">Diagnosis and treatment</label><textarea id="nt" name="note" rows="3" required></textarea><p><button class="b" name="add_note" value="1">Save note</button></p></form><?php endif;?>
<?php else:?><p class="sub">Select a patient to see their record.</p><?php endif;?></div></div><?php foot();
