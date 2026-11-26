<?php require 'config.php'; require 'layout.php'; require_login(); require 'rows.php';
$n=fn($sql)=>q($sql)->fetch()['c'];
$today=q('SELECT a.*,p.name pn,d.name dn FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id WHERE appt_date=CURDATE() AND status<>"Cancelled" ORDER BY appt_time')->fetchAll();
$up=q('SELECT a.*,p.name pn,d.name dn FROM appointments a JOIN patients p ON p.id=a.patient_id JOIN doctors d ON d.id=a.doctor_id WHERE appt_date>CURDATE() AND status="Scheduled" ORDER BY appt_date,appt_time LIMIT 5')->fetchAll();
head('Dashboard','index.php');?>
<h2>Today, <?=fmt_date(date('Y-m-d'))?></h2><div class="stats">
<div class="stat"><b><?=count($today)?></b><span>Appointments today</span></div>
<div class="stat"><b><?=$n('SELECT COUNT(*) c FROM appointments WHERE appt_date>CURDATE() AND status="Scheduled"')?></b><span>Upcoming</span></div>
<div class="stat"><b><?=$n('SELECT COUNT(*) c FROM patients')?></b><span>Registered patients</span></div>
<div class="stat"><b><?=$n('SELECT COUNT(*) c FROM doctors WHERE FIND_IN_SET(DAYOFWEEK(CURDATE())-1,working_days)')?></b><span>Doctors on duty</span></div></div>
<div class="panel"><h3>Today's schedule</h3><?php foreach($today as $a)appt_row($a);if(!$today)echo '<p class="sub">No appointments today.</p>';?></div>
<?php if(user()['role']==='staff'):$pend=q('SELECT a.*,p.name pn,d.name dn FROM appointments a JOIN patients p ON p.id=a.patient_id LEFT JOIN doctors d ON d.id=a.doctor_id WHERE status="Pending" ORDER BY a.id')->fetchAll();?>
<div class="panel" style="border-left:6px solid var(--warn)"><h3>Waiting for confirmation (<?=count($pend)?>)</h3><?php foreach($pend as $a)appt_row($a,true);if(!$pend)echo '<p class="sub">No patient requests waiting.</p>';?></div><?php endif;?>
<div class="panel"><h3>Next up</h3><?php foreach($up as $a)appt_row($a,true);if(!$up)echo '<p class="sub">Nothing scheduled ahead.</p>';?></div><?php foot();
