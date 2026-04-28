<?php
/* GOCOSYS — register.php */
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJSON(['success'=>false,'message'=>'Invalid method']);
$data=json_decode(file_get_contents('php://input'),true);
$name=trim($data['name']??''); $email=trim($data['email']??''); $password=$data['password']??'';

if(!$name||!$email||!$password)           sendJSON(['success'=>false,'message'=>'All fields required']);
if(!filter_var($email,FILTER_VALIDATE_EMAIL)) sendJSON(['success'=>false,'message'=>'Invalid email']);
if(strlen($password)<6)                    sendJSON(['success'=>false,'message'=>'Password min 6 chars']);

$chk=$conn->prepare("SELECT id FROM users WHERE email=?");
$chk->bind_param('s',$email);$chk->execute();$chk->store_result();
if($chk->num_rows>0) sendJSON(['success'=>false,'message'=>'Email already registered']);
$chk->close();

$words=explode(' ',$name);
$initials=strtoupper(substr($words[0],0,1).(isset($words[1])?substr($words[1],0,1):substr($words[0],1,1)));
$colors=['linear-gradient(135deg,#1565C0,#42A5F5)','linear-gradient(135deg,#6a1b9a,#ce93d8)','linear-gradient(135deg,#c8942a,#f0c040)','linear-gradient(135deg,#00695c,#80cbc4)','linear-gradient(135deg,#c62828,#ef9a9a)'];
$color=$colors[array_rand($colors)];
$hash=password_hash($password,PASSWORD_BCRYPT);

$ins=$conn->prepare("INSERT INTO users (name,email,password,role,avatar_initials,avatar_color) VALUES (?,?,?,'user',?,?)");
$ins->bind_param('sssss',$name,$email,$hash,$initials,$color);
if(!$ins->execute()) sendJSON(['success'=>false,'message'=>'Registration failed']);

$uid=$conn->insert_id;
$_SESSION['user_id']=$uid;$_SESSION['user_name']=$name;$_SESSION['user_email']=$email;
$_SESSION['user_role']='user';$_SESSION['user_initials']=$initials;$_SESSION['user_color']=$color;
sendJSON(['success'=>true,'user'=>['id'=>$uid,'name'=>$name,'email'=>$email,'role'=>'user','initials'=>$initials,'color'=>$color]]);