<?php
/* GOCOSYS — db.php */
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');        // XAMPP = empty password
define('DB_NAME', 'gocosys_blog');

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    die(json_encode(['success'=>false,'message'=>'DB error: '.$conn->connect_error]));
}
$conn->set_charset('utf8mb4');

function sendJSON($data, $code=200){
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}
function requireLogin(){
    if(!isset($_SESSION['user_id'])){
        sendJSON(['success'=>false,'message'=>'Login required','require_login'=>true],401);
    }
}