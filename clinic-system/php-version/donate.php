<?php // Patients can donate to the clinic by GCash, Maya or online banking. Staff check each donation before it counts.
require 'config.php'; require 'layout.php'; require_login('patient');
$pid=(int)user()['patient_id'];$M=['GCash','Maya','Online banking'];
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  $m=$_POST['method']??'';$amt=(float)($_POST['amount']??0);$ref=trim($_POST['reference']??'');$note=trim($_POST['note']??'');
  if(!in_array($m,$M)||$amt<=0||$amt>1000000||strlen($ref)<4){flash('Enter the payment method, an amount and the reference number from your receipt.');header('Location: donate.php');exit;}
  q('INSERT INTO donations(patient_id,method,amount,reference,note) VALUES(?,?,?,?,?)',[$pid,$m,round($amt,2),$ref,mb_substr($note,0,140)]);
  flash('Thank you! Your donation was sent to the clinic staff to check. It shows as Received once they confirm it.');header('Location: donate.php');exit;}
$acc=[];foreach(q('SELECT * FROM pay_accounts')->fetchAll() as $a)$acc[$a['method']]=$a;
$mine=q('SELECT * FROM donations WHERE patient_id=? ORDER BY id DESC',[$pid])->fetchAll();
head('Donate','donate.php');?><h2>Donate to the clinic</h2>
<form class="panel" method="post"><?=csrf_field()?>
<p class="sub">Donations are optional and help the clinic serve its patients. Send your donation from your GCash, Maya or online banking app to one of the clinic accounts below, then enter the amount and the reference number from your receipt. Clinic staff will check it and add it to the clinic balance.</p>
<?php foreach($M as $m):$a=$acc[$m]??[];?><div class="note"><b><?=e($m)?></b><?=!empty($a['bank_name'])?' · '.e($a['bank_name']):''?><div class="sub">Account name: <?=e($a['account_name']??'Not set')?></div><div class="sub"><?=$m==='Online banking'?'Account number':'Number'?>: <?=e($a['account_number']??'Not set')?></div></div><?php endforeach;?>
<div class="grid"><div><label for="m">Payment method used</label><select id="m" name="method"><?php foreach($M as $m):?><option><?=$m?></option><?php endforeach;?></select></div>
<div><label for="a">Amount (PHP)</label><input id="a" name="amount" type="number" min="1" step="0.01" required></div>
<div><label for="r">Reference number</label><input id="r" name="reference" autocomplete="off" required></div></div>
<label for="d">Description (optional)</label><input id="d" name="note" maxlength="140" placeholder="e.g. For the clinic medicine fund">
<p><button class="b">Submit donation</button></p></form>
<div class="panel"><h3>My donations</h3><?php foreach($mine as $x):?><div class="row"><span class="time" style="min-width:90px">₱<?=number_format($x['amount'],2)?></span><div class="grow"><b><?=e($x['method'])?></b><div class="sub"><?=fmt_date(substr($x['created_at'],0,10))?> · Ref <?=e($x['reference'])?><?=$x['note']?' · '.e($x['note']):''?></div></div><?=tag($x['status'])?></div><?php endforeach;
if(!$mine)echo '<p class="sub">No donations yet.</p>';?></div><?php foot();
