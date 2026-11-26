<?php
function head($title,$tab=''){$u=user();
$bgf=__DIR__.'/uploads/background.jpg';$g='color-mix(in srgb,var(--bg) 78%,transparent)';
$bgs=is_file($bgf)?' style="background-image:linear-gradient('.$g.','.$g.'),url(\'uploads/background.jpg?v='.filemtime($bgf).'\');background-size:cover;background-position:center;background-attachment:fixed"':'';
$links=['index.php'=>'Dashboard','appointments.php'=>'Appointments','patients.php'=>'Patients and records','doctors.php'=>'Doctors and midwives'];
if(!$u)$links=['duty.php'=>'Doctors on duty'];
if($u&&$u['role']==='patient')$links=['duty.php'=>'Doctors on duty','request.php'=>'Book appointment','my.php'=>'My appointments','donate.php'=>'Donate'];
if($u&&$u['role']==='staff')$links=['index.php'=>'Dashboard','book.php'=>'Book appointment']+array_slice($links,1)+['donations.php'=>'Donations','team.php'=>'Manage team'];
?><!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title><?=e($title)?> – Clinic One</title><link rel="stylesheet" href="style.css"></head><body<?=$bgs?>>
<header><div class="wrap"><?php if($u):?><span class="who"><?=e($u['name'])?> (<?=e($u['role'])?>) · <a href="logout.php">Log out</a></span><?php endif;?>
<div class="brand"><img src="logo.jpg" alt="Bagong Pilipinas logo" width="76" height="64"><div><h1>Clinic One</h1><p>Appointments, patient records and doctor schedules in one place</p></div></div>
<?php if(!$u):?><nav><a href="duty.php" <?=$tab==='duty.php'?'aria-current="true"':''?>>Doctors on duty</a><a href="login.php" <?=$tab==='login.php'?'aria-current="true"':''?>>Log in</a><a href="register.php" <?=$tab==='register.php'?'aria-current="true"':''?>>Create account</a></nav><?php else:?><nav><?php foreach($links as $f=>$l):?><a href="<?=$f?>" <?=$f===$tab?'aria-current="true"':''?>><?=$l?></a><?php endforeach;?></nav><?php endif;?></div></header>
<main class="wrap"><?php if($m=flash()):?><div class="msg" role="status"><?=e($m)?></div><?php endif;
}
function foot(){echo '</main></body></html>';}
