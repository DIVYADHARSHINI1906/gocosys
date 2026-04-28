<?php
/* GOCOSYS — check_session.php */
header('Content-Type: application/json; charset=utf-8');
session_start();
if(isset($_SESSION['user_id'])){
    echo json_encode(['loggedIn'=>true,'user'=>['id'=>$_SESSION['user_id'],'name'=>$_SESSION['user_name'],'email'=>$_SESSION['user_email'],'role'=>$_SESSION['user_role'],'initials'=>$_SESSION['user_initials'],'color'=>$_SESSION['user_color']]]);
} else {
    echo json_encode(['loggedIn'=>false]);
}