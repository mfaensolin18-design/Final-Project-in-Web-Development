<?php require 'config.php'; require 'layout.php'; require_login('patient');
$pid=(int)user()['patient_id'];
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  q('UPDATE appointments SET status="Cancelled" WHERE id=? AND patient_id=? AND status IN ("Scheduled","Pending")',[(int)$_POST['id'],$pid]);
  flash('Appointment cancelled.');header('Location: my.php');exit;}
$rows=q('SELECT a.*,d.name dn FROM appointments a LEFT JOIN doctors d ON d.id=a.doctor_id WHERE patient_id=? ORDER BY appt_date DESC,appt_time DESC',[$pid])->fetchAll();
head('My appointments','my.php');?><h2>My appointments</h2><div class="panel">
<?php foreach($rows as $a):?><div class="row"><span class="time"><?=$a['status']==='Pending'?'Pending':fmt_time($a['appt_time'])?></span><div class="grow"><b><?=$a['dn']?e($a['dn']):'Waiting for staff to confirm'?></b><div class="sub"><?=$a['appt_date']?fmt_date($a['appt_date']):'Staff will choose your date and time'?> · <?=e($a['reason']?:'No reason given')?></div></div><?=tag($a['status'])?>
<?php if(in_array($a['status'],['Scheduled','Pending'])):?><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>"><button class="g d">Cancel</button></form><?php endif;?></div><?php endforeach;
if(!$rows)echo '<p class="sub">You have no appointments yet. Use Book appointment to make one.</p>';?></div><?php foot();
