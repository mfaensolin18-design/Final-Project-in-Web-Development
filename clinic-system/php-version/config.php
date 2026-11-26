<?php
session_start();
const DB_HOST='localhost', DB_NAME='clinic_db', DB_USER='root', DB_PASS='';
function db(){static $p;if(!$p){$p=new PDO('mysql:host='.DB_HOST.';dbname='.DB_NAME.';charset=utf8mb4',DB_USER,DB_PASS,[PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC]);}return $p;}
function q($sql,$a=[]){$s=db()->prepare($sql);$s->execute($a);return $s;}
function e($s){return htmlspecialchars((string)$s,ENT_QUOTES,'UTF-8');}
function user(){return $_SESSION['user']??null;}
function require_login($roles=null){
  if(!user()){header('Location: duty.php');exit;}
  $r=user()['role'];$ok=$roles===null?in_array($r,['staff','doctor']):in_array($r,(array)$roles);
  if(!$ok){if($r==='patient'){header('Location: duty.php');exit;}http_response_code(403);exit('This page is not available for your role.');}
}
function csrf_field(){if(empty($_SESSION['csrf']))$_SESSION['csrf']=bin2hex(random_bytes(16));return '<input type="hidden" name="csrf" value="'.$_SESSION['csrf'].'">';}
function check_csrf(){if(!hash_equals($_SESSION['csrf']??'',$_POST['csrf']??'')){http_response_code(400);exit('Invalid form token. Go back and try again.');}}
function flash($m=null){if($m!==null){$_SESSION['flash']=$m;return;}$f=$_SESSION['flash']??'';unset($_SESSION['flash']);return $f;}
function fmt_time($t){return date('g:i A',strtotime($t));}
function fmt_date($d){return date('D, M j, Y',strtotime($d));}
function tag($s){return '<span class="tag '.e($s).'">'.e($s).'</span>';}
