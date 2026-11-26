<?php // Run once after importing database.sql, then delete this file.
require 'config.php';
if(q('SELECT COUNT(*) c FROM users')->fetch()['c']>0)exit('Users already exist. Delete install.php.');
$ins='INSERT INTO users(username,password_hash,role,name,doctor_id) VALUES(?,?,?,?,?)';
q($ins,['staff',password_hash('staff123',PASSWORD_DEFAULT),'staff','Front desk',null]);
q($ins,['drsantos',password_hash('doctor123',PASSWORD_DEFAULT),'doctor','Dr. Maria Santos',1]);
q($ins,['midwife',password_hash('midwife123',PASSWORD_DEFAULT),'doctor','Midwife Rosa Villanueva',4]);
echo 'Done. Demo users created. <a href="login.php">Go to login</a>. Now delete install.php.';
