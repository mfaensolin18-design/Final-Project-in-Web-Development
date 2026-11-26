<?php // Staff: donation balance, check incoming donations, and set the receiving accounts shown to patients.
require 'config.php'; require 'layout.php'; require_login('staff');
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  if(isset($_POST['do'],$_POST['id'])&&in_array($_POST['do'],['Received','Rejected'])){
    q('UPDATE donations SET status=? WHERE id=? AND status="Pending"',[$_POST['do'],(int)$_POST['id']]);flash($_POST['do']==='Received'?'Donation added to the clinic balance.':'Donation rejected.');}
  $uid=(int)user()['id'];
  if(isset($_POST['wd_setup'])){$a=$_POST['wpass']??'';
    if(strlen($a)<6)flash('The withdrawal password must have at least 6 characters.');
    elseif($a!==($_POST['wpass2']??''))flash('The passwords do not match.');
    else{q('UPDATE users SET withdraw_hash=? WHERE id=? AND withdraw_hash IS NULL',[password_hash($a,PASSWORD_DEFAULT),$uid]);flash('Withdrawal password created. You can now make withdrawals.');}}
  if(isset($_POST['wd_send'])){
    $prov=$_POST['provider']??'';$wallet=in_array($prov,['GCash','Maya']);$bank=in_array($prov,['BDO','PBI','MetroBank','LandBank']);
    $amt=round((float)($_POST['amount']??0),2);$name=trim($_POST['account_name']??'');$num=preg_replace('/[\s-]/','',$_POST['account_number']??'');
    $hash=q('SELECT withdraw_hash FROM users WHERE id=?',[$uid])->fetch()['withdraw_hash'];
    $_SESSION['wd_fail']=$_SESSION['wd_fail']??0;
    if(!$hash)$err='Create a withdrawal password first.';
    elseif($_SESSION['wd_fail']>=5)$err='Too many wrong passwords. Log out and try again later.';
    elseif(!$wallet&&!$bank)$err='Choose online wallet or bank transfer.';
    elseif($amt<=0)$err='Enter the amount to withdraw.';
    elseif($name==='')$err=$wallet?"Enter the $prov name.":'Enter the account name.';
    elseif($wallet&&!preg_match('/^09\d{9}$/',$num))$err="Enter a valid $prov number (11 digits, starting with 09).";
    elseif($bank&&!preg_match('/^\d{8,20}$/',$num))$err='Enter a valid bank account number (8 to 20 digits).';
    elseif(!password_verify($_POST['wpassword']??'',$hash)){$_SESSION['wd_fail']++;$err='Wrong withdrawal password.';}
    else{
      db()->beginTransaction();q('SELECT id FROM users WHERE id=? FOR UPDATE',[$uid]);
      $av=q('SELECT (SELECT COALESCE(SUM(amount),0) FROM donations WHERE status="Received")-(SELECT COALESCE(SUM(amount),0) FROM withdrawals) a')->fetch()['a'];
      if($amt>$av){db()->rollBack();$err='The amount is more than the available balance (₱'.number_format($av,2).').';}
      else{q('INSERT INTO withdrawals(user_id,kind,provider,account_name,account_number,amount) VALUES(?,?,?,?,?,?)',[$uid,$wallet?'Online wallet':'Bank transfer',$prov,$name,$num,$amt]);db()->commit();$_SESSION['wd_fail']=0;
        $err='';flash('Withdrawal of ₱'.number_format($amt,2)." to $prov recorded. Send the money from the clinic account yourself. This system keeps the record and the balance.");}}
    if(!empty($err))flash($err);}
  if(isset($_POST['save_accounts'])){
    foreach(['GCash','Maya','Online banking'] as $m){$k=md5($m);
      q('UPDATE pay_accounts SET bank_name=?,account_name=?,account_number=? WHERE method=?',[trim($_POST["bank_$k"]??''),trim($_POST["name_$k"]??''),trim($_POST["num_$k"]??''),$m]);}
    flash('Receiving accounts saved.');}
  header('Location: donations.php');exit;}
$bal=q('SELECT COALESCE(SUM(amount),0) s,COUNT(*) c FROM donations WHERE status="Received"')->fetch();
$pen=q('SELECT COALESCE(SUM(amount),0) s,COUNT(*) c FROM donations WHERE status="Pending"')->fetch();
$wdsum=q('SELECT COALESCE(SUM(amount),0) s FROM withdrawals')->fetch()['s'];$avail=$bal['s']-$wdsum;
$hasPw=(bool)q('SELECT withdraw_hash FROM users WHERE id=?',[(int)user()['id']])->fetch()['withdraw_hash'];
$wds=q('SELECT w.*,u.name un FROM withdrawals w JOIN users u ON u.id=w.user_id ORDER BY w.id DESC')->fetchAll();
$list=q('SELECT d.*,p.name pn FROM donations d JOIN patients p ON p.id=d.patient_id ORDER BY d.id DESC')->fetchAll();
$accs=q('SELECT * FROM pay_accounts')->fetchAll();
head('Donations','donations.php');?><h2>Donations</h2>
<div class="stats"><div class="stat"><b>₱<?=number_format($avail,2)?></b><span>Available balance</span></div><div class="stat"><b>₱<?=number_format($wdsum,2)?></b><span>Total withdrawn</span></div>
<div class="stat"><b><?=$pen['c']?></b><span>Waiting to be checked (₱<?=number_format($pen['s'],2)?>)</span></div>
<div class="stat"><b><?=$bal['c']?></b><span>Donations received</span></div></div>
<div class="panel"><h3>Donations</h3><p class="sub">Compare the amount and reference number with your GCash, Maya or bank records before marking a donation as received.</p>
<?php foreach($list as $x):?><div class="row"><span class="time" style="min-width:90px">₱<?=number_format($x['amount'],2)?></span><div class="grow"><b><?=e($x['pn'])?></b><div class="sub"><?=e($x['method'])?> · <?=fmt_date(substr($x['created_at'],0,10))?> · Ref <?=e($x['reference'])?><?=$x['note']?' · '.e($x['note']):''?></div></div><?=tag($x['status'])?>
<?php if($x['status']==='Pending'):?><form method="post"><?=csrf_field()?><input type="hidden" name="id" value="<?=$x['id']?>"><button class="g" name="do" value="Received">Mark received</button> <button class="g d" name="do" value="Rejected">Reject</button></form><?php endif;?></div><?php endforeach;
if(!$list)echo '<p class="sub">No donations yet.</p>';?></div>
<?php if(!$hasPw):?><form class="panel" method="post"><?=csrf_field()?><h3>Withdraw donations</h3><p class="sub">Create a withdrawal password first. You will need it for every withdrawal.</p>
<div class="grid"><div><label for="wp1">Withdrawal password (at least 6 characters)</label><input id="wp1" name="wpass" type="password" autocomplete="new-password" required></div>
<div><label for="wp2">Confirm password</label><input id="wp2" name="wpass2" type="password" autocomplete="new-password" required></div></div>
<p><button class="b" name="wd_setup" value="1">Create withdrawal password</button></p></form>
<?php else:?><form class="panel" method="post"><?=csrf_field()?><h3>Withdraw donations</h3><p class="sub">Available balance: <b>₱<?=number_format($avail,2)?></b>. This records the withdrawal and lowers the balance. You must send the money from the clinic's own account.</p>
<div class="grid"><div><label for="wpv">Withdraw by</label><select id="wpv" name="provider"><optgroup label="Online wallet"><option>GCash</option><option>Maya</option></optgroup><optgroup label="Bank transfer"><option>BDO</option><option>PBI</option><option>MetroBank</option><option>LandBank</option></optgroup></select></div>
<div><label for="wa">Amount (PHP)</label><input id="wa" name="amount" type="number" min="1" step="0.01" required></div>
<div><label for="wn">Account name (GCash or Maya name, or bank account name)</label><input id="wn" name="account_name" required></div>
<div><label for="wnum">Number (GCash or Maya number, or bank account number)</label><input id="wnum" name="account_number" inputmode="numeric" required></div>
<div><label for="wpw">Withdrawal password</label><input id="wpw" name="wpassword" type="password" autocomplete="off" required></div></div>
<p><button class="b" name="wd_send" value="1">Withdraw</button></p></form><?php endif;?>
<div class="panel"><h3>Withdrawal history</h3><?php foreach($wds as $w):?><div class="row"><span class="time" style="min-width:90px">₱<?=number_format($w['amount'],2)?></span><div class="grow"><b><?=e($w['provider'])?></b> <span class="sub"><?=e($w['kind'])?></span><div class="sub"><?=e($w['account_name'])?> · ••••<?=e(substr($w['account_number'],-4))?> · <?=fmt_date(substr($w['created_at'],0,10))?> · by <?=e($w['un'])?></div></div></div><?php endforeach;if(!$wds)echo '<p class="sub">No withdrawals yet.</p>';?></div>
<form class="panel" method="post"><?=csrf_field()?><h3>Receiving accounts shown to patients</h3><p class="sub">Replace the sample numbers with the clinic's real accounts.</p><div class="grid">
<?php foreach($accs as $a):$k=md5($a['method']);?><div><label for="n<?=$k?>"><?=e($a['method'])?> account name</label><input id="n<?=$k?>" name="name_<?=$k?>" value="<?=e($a['account_name'])?>"></div>
<div><label for="u<?=$k?>"><?=e($a['method'])?> <?=$a['method']==='Online banking'?'account number':'number'?></label><input id="u<?=$k?>" name="num_<?=$k?>" value="<?=e($a['account_number'])?>"></div>
<div><label for="b<?=$k?>"><?=$a['method']==='Online banking'?'Bank name':'Bank name (not needed)'?></label><input id="b<?=$k?>" name="bank_<?=$k?>" value="<?=e($a['bank_name'])?>"></div><?php endforeach;?></div>
<p><button class="b" name="save_accounts" value="1">Save accounts</button></p></form><?php foot();
