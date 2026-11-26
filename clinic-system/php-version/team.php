<?php // Staff only: add front desk staff, doctors and midwives (with their login accounts).
require 'config.php'; require 'layout.php'; require_login('staff');
$D=['Sun','Mon','Tue','Wed','Thu','Fri','Sat'];
if($_SERVER['REQUEST_METHOD']==='POST'&&(isset($_POST['bg_upload'])||isset($_POST['bg_remove']))){check_csrf();
  $dir=__DIR__.'/uploads';$file=$dir.'/background.jpg';$err='';
  if(isset($_POST['bg_remove'])){if(is_file($file))unlink($file);flash('Background removed.');}
  else{$f=$_FILES['bg']??null;
    if(!$f||$f['error']!==UPLOAD_ERR_OK)$err='Choose an image to upload (under 5 MB).';
    elseif($f['size']>5*1024*1024)$err='That image is too large. Choose one under 5 MB.';
    elseif(!function_exists('imagecreatefromstring'))$err='The PHP GD extension is not enabled on this server.';
    else{$info=@getimagesize($f['tmp_name']);
      if(!$info||!in_array($info[2],[IMAGETYPE_JPEG,IMAGETYPE_PNG,IMAGETYPE_WEBP]))$err='Choose a JPG, PNG or WebP image.';
      else{$im=@imagecreatefromstring(file_get_contents($f['tmp_name']));
        if(!$im)$err='That file could not be read as an image.';
        else{$w=imagesx($im);$h=imagesy($im);$k=min(1,1600/$w);$nw=(int)round($w*$k);$nh=(int)round($h*$k);
          $out=imagecreatetruecolor($nw,$nh);imagefill($out,0,0,imagecolorallocate($out,255,255,255));imagecopyresampled($out,$im,0,0,0,0,$nw,$nh,$w,$h);
          if(!is_dir($dir))mkdir($dir,0755,true);
          imagejpeg($out,$file,82);flash('Background updated. Everyone who opens the portal will see it.');}}}
    if($err)flash($err);}
  header('Location: team.php');exit;}
if($_SERVER['REQUEST_METHOD']==='POST'){check_csrf();
  $name=trim($_POST['name']??'');$role=$_POST['role']??'staff';$un=trim($_POST['username']??'');$pw=$_POST['password']??'';
  $days=array_values(array_intersect(['0','1','2','3','4','5','6'],array_map('strval',$_POST['days']??[])));$err='';
  if(!in_array($role,['staff','Doctor','Midwife']))$err='Choose a role.';
  elseif($name===''||$un==='')$err='Enter a name and a username.';
  elseif(strlen($pw)<6)$err='Password must have at least 6 characters.';
  elseif(q('SELECT id FROM users WHERE username=?',[$un])->fetch())$err='That username is taken.';
  elseif($role!=='staff'&&!$days)$err='Choose at least one working day.';
  if($err){flash($err);header('Location: team.php');exit;}
  $did=null;
  if($role!=='staff'){$sp=trim($_POST['specialty']??'');if($sp==='')$sp=$role==='Midwife'?'Maternal and Newborn Care':'General Medicine';
    q('INSERT INTO doctors(name,specialty,kind,working_days) VALUES(?,?,?,?)',[$name,$sp,$role,implode(',',$days)]);$did=db()->lastInsertId();}
  q('INSERT INTO users(username,password_hash,role,name,doctor_id) VALUES(?,?,?,?,?)',[$un,password_hash($pw,PASSWORD_DEFAULT),$role==='staff'?'staff':'doctor',$name,$did]);
  flash("Added $name ($role). They can now log in as $un.");header('Location: team.php');exit;}
$docs=q('SELECT * FROM doctors ORDER BY kind,name')->fetchAll();
$users=q('SELECT u.username,u.name,u.role,d.kind FROM users u LEFT JOIN doctors d ON d.id=u.doctor_id WHERE u.role<>"patient" ORDER BY u.role,u.name')->fetchAll();
head('Manage team','team.php');?><h2>Manage team</h2>
<form class="panel" method="post" enctype="multipart/form-data"><?=csrf_field()?><h3>Portal background</h3>
<p class="sub">Upload a picture to use as the background of the whole portal. Patients, doctors, midwives and visitors will all see it.</p>
<?php if(is_file(__DIR__.'/uploads/background.jpg')):?><img src="uploads/background.jpg?v=<?=filemtime(__DIR__.'/uploads/background.jpg')?>" alt="Current portal background preview" style="max-width:100%;max-height:160px;border-radius:8px;border:1px solid var(--line)"><?php endif;?>
<label for="bg">Choose image (JPG, PNG or WebP, under 5 MB)</label><input id="bg" name="bg" type="file" accept="image/*">
<p><button class="b" name="bg_upload" value="1">Upload background</button> <?php if(is_file(__DIR__.'/uploads/background.jpg')):?><button class="g d" name="bg_remove" value="1">Remove background</button><?php endif;?></p></form>
<form class="panel" method="post"><?=csrf_field()?><h3>Add a staff member, doctor or midwife</h3><p class="sub">Only staff can create these accounts. Patients create their own accounts on the sign-up page.</p>
<div class="grid"><div><label for="n">Full name</label><input id="n" name="name" required></div>
<div><label for="r">Role</label><select id="r" name="role"><option value="staff">Front desk staff</option><option>Doctor</option><option>Midwife</option></select></div>
<div><label for="s">Specialty (doctors and midwives)</label><input id="s" name="specialty"></div>
<div><label for="u">Username</label><input id="u" name="username" autocomplete="off" required></div>
<div><label for="p">Password (at least 6 characters)</label><input id="p" name="password" type="password" autocomplete="new-password" required></div></div>
<label>Working days (doctors and midwives)</label><div class="sched"><?php foreach($D as $i=>$n):?><label style="display:inline-flex;gap:4px;margin:0"><input type="checkbox" name="days[]" value="<?=$i?>" style="width:auto" <?=$i>0&&$i<6?'checked':''?>><?=$n?></label><?php endforeach;?></div>
<p><button class="b">Add to team</button></p></form>
<div class="panel"><h3>Doctors and midwives</h3><?php foreach($docs as $d):$w=explode(',',$d['working_days']);?><div class="row"><div class="grow"><b><?=e($d['name'])?></b><div class="sub"><?=e($d['kind'])?> · <?=e($d['specialty'])?></div></div>
<div class="sched"><?php foreach($D as $i=>$n):?><span class="<?=in_array($i,$w)?'':'off'?>"><?=$n?></span><?php endforeach;?></div></div><?php endforeach;?></div>
<div class="panel"><h3>Login accounts</h3><?php foreach($users as $x):?><div class="row"><div class="grow"><b><?=e($x['name'])?></b><div class="sub"><?=e($x['username'])?></div></div><span class="tag"><?=$x['role']==='staff'?'Front desk':e($x['kind']?:'Doctor')?></span></div><?php endforeach;?></div><?php foot();
