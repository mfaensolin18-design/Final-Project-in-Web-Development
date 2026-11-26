<?php // Public page: anyone can see which doctors are on duty. No login needed.
require 'config.php'; require 'layout.php';
$days=[];for($i=0;$i<7;$i++){$dt=date('Y-m-d',strtotime("+$i day"));$w=date('w',strtotime($dt));
  $days[$dt]=array_values(array_filter(q('SELECT * FROM doctors ORDER BY name')->fetchAll(),fn($d)=>in_array($w,explode(',',$d['working_days']))));}
$today=$days[date('Y-m-d')];$next=null;foreach(array_slice($days,1,null,true) as $dt=>$ds){if($ds){$next=[$dt,$ds];break;}}
head('Doctors on duty','duty.php');?>
<div class="panel" style="border-left:6px solid var(<?=$today?'--ok':'--warn'?>)"><h2><?=user()?'Doctors on duty today':'Clinic status today'?></h2>
<p class="sub"><?=fmt_date(date('Y-m-d'))?> · Clinic hours 9:00 AM to 5:00 PM, closed 12:00 to 1:00 PM</p>
<?php if($today):foreach($today as $d):?><div class="row"><div class="grow"><b><?=e($d['name'])?></b><div class="sub"><?=e($d['kind'])?> · <?=e($d['specialty'])?></div></div><span class="tag Completed">On duty</span></div><?php endforeach;
else:?><p><b class="err">No doctors or midwives on duty today. Please do not come to the clinic.</b></p>
<?php if($next):?><p>Next doctor on duty: <?=e(implode(', ',array_column($next[1],'name')))?> on <?=fmt_date($next[0])?>.</p><?php endif;endif;?>
<h3 style="margin-top:14px">Next 7 days</h3>
<?php foreach($days as $dt=>$ds):?><div class="row"><span class="time" style="min-width:150px"><?=date('D, M j',strtotime($dt))?></span><div class="grow sub"><?=$ds?e(implode(', ',array_column($ds,'name'))):'No doctors on duty'?></div></div><?php endforeach;?></div>
<?php if(!user()):?><p class="sub">To book an appointment, <a href="register.php">create a free patient account</a> or <a href="login.php">log in</a>.</p><?php endif;?><?php if(user()&&user()['role']==='patient'):?><p><a class="b" href="request.php" style="text-decoration:none;display:inline-block">Book an appointment</a></p><?php endif;foot();
