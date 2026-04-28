<?php
/* GOCOSYS — login.php */
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'db.php';

if($_SERVER['REQUEST_METHOD']!=='POST') sendJSON(['success'=>false,'message'=>'Invalid method']);
$data=json_decode(file_get_contents('php://input'),true);
$email=trim($data['email']??''); $password=$data['password']??'';

if(!$email||!$password) sendJSON(['success'=>false,'message'=>'Email and password required']);

$stmt=$conn->prepare("SELECT id,name,email,password,role,avatar_initials,avatar_color FROM users WHERE email=?");
$stmt->bind_param('s',$email);$stmt->execute();$res=$stmt->get_result();
if($res->num_rows===0) sendJSON(['success'=>false,'message'=>'Invalid email or password']);

$user=$res->fetch_assoc();$stmt->close();
if(!password_verify($password,$user['password'])) sendJSON(['success'=>false,'message'=>'Invalid email or password']);

$_SESSION['user_id']=$user['id'];$_SESSION['user_name']=$user['name'];$_SESSION['user_email']=$user['email'];
$_SESSION['user_role']=$user['role'];$_SESSION['user_initials']=$user['avatar_initials'];$_SESSION['user_color']=$user['avatar_color'];
sendJSON(['success'=>true,'user'=>['id'=>$user['id'],'name'=>$user['name'],'email'=>$user['email'],'role'=>$user['role'],'initials'=>$user['avatar_initials'],'color'=>$user['avatar_color']]]);