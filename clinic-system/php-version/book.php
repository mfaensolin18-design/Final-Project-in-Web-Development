<?php require 'config.php'; require 'layout.php'; require_login('staff');
$docs=q('SELECT * FROM doctors')->fetchAll();$pts=q('SELECT id,name FROM patients ORDER BY name')->fetchAll();
$did=(int)($_REQUEST['doctor_id']??$docs[0]['id']);$date=$_REQUEST['date']??date('Y-m-d',strtotime('+1 day'));
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||$date<date('Y-m-d'))$date=date('Y-m-d',strtotime('+1 day'));
$doc=q('SELECT * FROM doctors WHERE id=?',[$did])->fetch();
$works=$doc&&in_array(date('w',strtotime($date)),explode(',',$doc['working_days']));
$slots=[];for($h=9;$h<17;$h++){if($h==12)continue;$slots[]=sprintf('%02d:00',$h);$slots[]=sprintf('%02d:30',$h);}
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['save'])){check_csrf();$err='';
  $slot=$_POST['slot']??'';$pid=user()['role']==='patient'?user()['patient_id']:($_POST['patient_id']??'');
  if(!$works)$err='This doctor does not see patients on that day.';
  elseif(!in_array($slot,$slots))$err='Choose a time slot first.';
  else{
    if($pid==='new'){$name=trim($_POST['new_name']??'');if($name==='')$err='Enter the new patient\'s name.';
      else{q('INSERT INTO patients(name,age,phone) VALUES(?,?,?)',[$name,(int)$_POST['new_age'],trim($_POST['new_phone']??'')]);$pid=db()->lastInsertId();}}
    if(!$err){try{q('INSERT INTO appointments(patient_id,doctor_id,appt_date,appt_time,reason) VALUES(?,?,?,?,?)',[(int)$pid,$did,$date,$slot,trim($_POST['reason']??'')]);
      flash('Appointment booked for '.fmt_date($date).' at '.fmt_time($slot).'.');header('Location: book.php');exit;}
      catch(PDOException $x){$err=$x->getCode()==='23000'?'That slot was just taken. Choose another time.':'Could not save the appointment.';}}}
  flash($err);header('Location: book.php?doctor_id='.$did.'&date='.$date);exit;}
$taken=array_column(q('SELECT TIME_FORMAT(appt_time,"%H:%i") t FROM appointments WHERE doctor_id=? AND appt_date=? AND status<>"Cancelled"',[$did,$date])->fetchAll(),'t');
head('Book appointment','book.php');?><h2>Book an appointment</h2>
<form class="panel" method="get"><div class="grid"><div><label for="d">Doctor</label><select id="d" name="doctor_id"><?php foreach($docs as $d):?><option value="<?=$d['id']?>" <?=$d['id']==$did?'selected':''?>><?=e($d['name'])?> (<?=e($d['specialty'])?>)</option><?php endforeach;?></select></div>
<div><label for="dt">Date</label><input id="dt" type="date" name="date" min="<?=date('Y-m-d')?>" value="<?=e($date)?>"></div></div><p><button class="g">Show available times</button></p></form>
<form class="panel" method="post"><?=csrf_field()?><input type="hidden" name="doctor_id" value="<?=$did?>"><input type="hidden" name="date" value="<?=e($date)?>">
<h3>Times on <?=fmt_date($date)?></h3>
<?php if(!$works):?><p class="err"><?=e($doc['name'])?> does not see patients on <?=date('l',strtotime($date))?>. Pick another date or doctor.</p><?php else:?>
<div class="slots"><?php foreach($slots as $s):?><label><input type="radio" name="slot" value="<?=$s?>" <?=in_array($s,$taken)?'disabled':''?>><span><?=fmt_time($s)?></span></label><?php endforeach;?></div>
<p class="sub">Struck-through times are already taken.</p><?php endif;?>
<div class="grid"><?php if(user()['role']==='staff'):?><div><label for="p">Patient</label><select id="p" name="patient_id"><?php foreach($pts as $p):?><option value="<?=$p['id']?>"><?=e($p['name'])?></option><?php endforeach;?><option value="new">New patient…</option></select></div><?php endif;?>
<div><label for="r">Reason for visit</label><input id="r" name="reason" maxlength="255"></div></div>
<?php if(user()['role']==='staff'):?><div class="grid"><div><label for="nn">New patient name (if selected)</label><input id="nn" name="new_name"></div><div><label for="na">Age</label><input id="na" type="number" name="new_age" min="0" max="120"></div><div><label for="np">Phone</label><input id="np" name="new_phone"></div></div><?php endif;?>
<p><button class="b" name="save" value="1">Confirm appointment</button></p></form><?php foot();
