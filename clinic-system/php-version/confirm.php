<?php // Staff arrange the doctor, date and time for a pending request, based on who is on duty.
require 'config.php'; require 'layout.php'; require_login('staff');
$id=(int)($_REQUEST['id']??0);
$a=q('SELECT a.*,p.name pn,p.age,p.gender FROM appointments a JOIN patients p ON p.id=a.patient_id WHERE a.id=? AND a.status="Pending"',[$id])->fetch();
if(!$a){flash('That request was already handled.');header('Location: appointments.php');exit;}
$date=$_REQUEST['date']??date('Y-m-d',strtotime('+1 day'));
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$date)||$date<date('Y-m-d'))$date=date('Y-m-d',strtotime('+1 day'));
$w=date('w',strtotime($date));
$onduty=array_values(array_filter(q('SELECT * FROM doctors ORDER BY name')->fetchAll(),fn($d)=>in_array($w,explode(',',$d['working_days']))));
$ids=array_column($onduty,'id');$did=(int)($_REQUEST['doctor_id']??0);if(!in_array($did,$ids))$did=$ids[0]??0;
$slots=[];for($h=9;$h<17;$h++){if($h==12)continue;$slots[]=sprintf('%02d:00',$h);$slots[]=sprintf('%02d:30',$h);}
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  if(($_POST['do']??'')==='decline'){q('UPDATE appointments SET status="Cancelled" WHERE id=? AND status="Pending"',[$id]);flash('Request declined.');header('Location: appointments.php');exit;}
  $slot=$_POST['slot']??'';$err='';
  if(!$did||!in_array($slot,$slots))$err='Choose a doctor on duty and a time slot.';
  else{try{q('UPDATE appointments SET doctor_id=?,appt_date=?,appt_time=?,status="Scheduled" WHERE id=? AND status="Pending"',[$did,$date,$slot,$id]);
    flash('Confirmed: '.$a['pn'].' on '.fmt_date($date).' at '.fmt_time($slot).'.');header('Location: appointments.php');exit;}
    catch(PDOException $x){$err=$x->getCode()==='23000'?'That slot was just taken. Choose another time.':'Could not confirm the appointment.';}}
  flash($err);header('Location: confirm.php?id='.$id.'&date='.$date.'&doctor_id='.$did);exit;}
$taken=$did?array_column(q('SELECT TIME_FORMAT(appt_time,"%H:%i") t FROM appointments WHERE doctor_id=? AND appt_date=? AND status<>"Cancelled"',[$did,$date])->fetchAll(),'t'):[];
head('Arrange appointment','appointments.php');?><h2>Arrange appointment</h2>
<div class="panel"><h3><?=e($a['pn'])?></h3><div class="sub"><?=$a['age']?e($a['age']).' yrs':'Age not given'?> · <?=e($a['gender']?:'Gender not given')?></div><p>Reason for visit: <?=e($a['reason']?:'Not given')?></p></div>
<form class="panel" method="get"><input type="hidden" name="id" value="<?=$id?>"><div class="grid">
<div><label for="dt">Date</label><input id="dt" type="date" name="date" min="<?=date('Y-m-d')?>" value="<?=e($date)?>"></div>
<div><label for="d">Doctor on duty that day</label><?php if($onduty):?><select id="d" name="doctor_id"><?php foreach($onduty as $d):?><option value="<?=$d['id']?>" <?=$d['id']==$did?'selected':''?>><?=e($d['name'])?> (<?=e($d['specialty'])?>)</option><?php endforeach;?></select><?php else:?><p class="err">No doctors on duty on <?=date('l',strtotime($date))?>. Choose another date.</p><?php endif;?></div></div>
<p><button class="g">Show available times</button></p></form>
<?php if($onduty):?><form class="panel" method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$id?>"><input type="hidden" name="date" value="<?=e($date)?>"><input type="hidden" name="doctor_id" value="<?=$did?>">
<h3>Times on <?=fmt_date($date)?></h3><div class="slots"><?php foreach($slots as $s):?><label><input type="radio" name="slot" value="<?=$s?>" <?=in_array($s,$taken)?'disabled':''?>><span><?=fmt_time($s)?></span></label><?php endforeach;?></div>
<p class="sub">Struck-through times are already taken.</p><p><button class="b">Confirm appointment</button></p></form><?php endif;?>
<form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$id?>"><button class="g d" name="do" value="decline">Decline request</button></form><?php foot();
