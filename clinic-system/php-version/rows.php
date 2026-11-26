<?php function appt_row($a,$actions=false){?>
<div class="row"><span class="time"><?=$a['status']==='Pending'?'Pending':fmt_time($a['appt_time'])?></span><div class="grow"><b><?=e($a['pn'])?></b>
<div class="sub"><?=e($a['dn']??'No doctor assigned yet')?> · <?=e($a['reason']?:'No reason given')?><?=$actions&&$a['appt_date']?' · '.fmt_date($a['appt_date']):''?></div></div><?=tag($a['status'])?>
<?php if($actions&&$a['status']==='Pending'&&user()['role']==='staff'):?><a class="g" href="confirm.php?id=<?=$a['id']?>">Arrange date and time</a><?php endif;?>
<?php if($actions&&$a['status']==='Scheduled'):?><form method="post" action="appointments.php"><?=csrf_field()?><input type="hidden" name="id" value="<?=$a['id']?>">
<button class="g" name="do" value="Completed">Mark completed</button> <?php if(user()['role']==='staff'):?><button class="g d" name="do" value="Cancelled">Cancel</button><?php endif;?></form><?php endif;?></div><?php }
