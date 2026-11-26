<?php require 'config.php'; require 'layout.php'; require_login(); require 'rows.php';
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();$do=$_POST['do']??'';
  $id=(int)$_POST['id'];$staff=user()['role']==='staff';
  if($do==='Completed'){q('UPDATE appointments SET status="Completed" WHERE id=? AND status="Scheduled"',[$id]);flash('Appointment marked Completed.');}
  elseif($staff&&$do==='Cancelled'){q('UPDATE appointments SET status="Cancelled" WHERE id=? AND status IN ("Scheduled","Pending")',[$id]);flash('Appointment cancelled.');}
  header('Location: appointments.php');exit;}
$f=in_array($_GET['status']??'',['Pending','Scheduled','Completed','Cancelled'])?$_GET['status']:'';
$rows=q('SELECT a.*,p.name pn,d.name dn FROM appointments a JOIN patients p ON p.id=a.patient_id LEFT JOIN doctors d ON d.id=a.doctor_id '.($f?'WHERE status=?':'').' ORDER BY (status="Pending") DESC,appt_date DESC,appt_time DESC',$f?[$f]:[])->fetchAll();
head('Appointments','appointments.php');?><h2>Appointments</h2>
<p><?php foreach(['All'=>'','Pending'=>'Pending','Scheduled'=>'Scheduled','Completed'=>'Completed','Cancelled'=>'Cancelled'] as $l=>$v):?><a class="g" href="?status=<?=$v?>" <?=$f===$v?'style="font-weight:700;background:var(--soft)"':''?>><?=$l?></a> <?php endforeach;?></p>
<div class="panel"><?php foreach($rows as $a)appt_row($a,true);if(!$rows)echo '<p class="sub">No appointments match this filter.</p>';?></div><?php foot();
