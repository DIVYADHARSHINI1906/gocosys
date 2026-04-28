<?php
/* GOCOSYS — logout.php */
header('Content-Type: application/json; charset=utf-8');
session_start();
session_destroy();
sendJSON(['success'=>true,'message'=>'Logged out']);
function sendJSON($d){ echo json_encode($d); exit; }