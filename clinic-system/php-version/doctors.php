<?php require 'config.php'; require 'layout.php'; require_login();$D=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
head('Doctors','doctors.php');?><h2>Doctors and schedules</h2>
<?php foreach(q('SELECT d.*,(SELECT COUNT(*) FROM appointments a WHERE a.doctor_id=d.id AND status="Scheduled" AND appt_date BETWEEN CURDATE() AND CURDATE()+INTERVAL 7 DAY) booked FROM doctors d')->fetchAll() as $d):$w=explode(',',$d['working_days']);?>
<div class="panel"><h3><?=e($d['name'])?></h3><div class="sub"><?=e($d['kind'])?> · <?=e($d['specialty'])?> · 9:00 AM to 5:00 PM, closed 12:00 to 1:00 PM</div>
<div class="sched" style="margin-top:6px"><?php foreach($D as $i=>$n):?><span class="<?=in_array($i,$w)?'':'off'?>"><?=$n?></span><?php endforeach;?></div>
<p class="sub">Booked in the next 7 days: <?=$d['booked']?></p></div><?php endforeach;foot();
