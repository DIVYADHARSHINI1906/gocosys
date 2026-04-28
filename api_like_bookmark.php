<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — api_like_bookmark.php
   GET  ?action=counts&id=5  → like/bookmark counts
   POST ?action=like&id=5    → toggle like
   POST ?action=bookmark&id=5→ toggle bookmark
═══════════════════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
session_start();
require_once 'db.php';

$action = $_GET['action']     ?? '';
$artId  = (int)($_GET['id']   ?? 0);
if (!$artId) sendJSON(['success'=>false,'message'=>'Invalid article ID'], 400);

/* ── GET counts (public) ── */
if ($action === 'counts') {
    $lc = $conn->query("SELECT COUNT(*) c FROM likes     WHERE article_id=$artId")->fetch_assoc()['c'];
    $bc = $conn->query("SELECT COUNT(*) c FROM bookmarks WHERE article_id=$artId")->fetch_assoc()['c'];
    $liked = $bookmarked = false;
    if (isset($_SESSION['user_id'])) {
        $uid = (int)$_SESSION['user_id'];
        $liked    = $conn->query("SELECT id FROM likes     WHERE user_id=$uid AND article_id=$artId")->num_rows > 0;
        $bookmarked = $conn->query("SELECT id FROM bookmarks WHERE user_id=$uid AND article_id=$artId")->num_rows > 0;
    }
    sendJSON(['success'=>true,'like_count'=>(int)$lc,'bookmark_count'=>(int)$bc,'user_liked'=>$liked,'user_bookmarked'=>$bookmarked]);
}

/* ── toggle like / bookmark (login required) ── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') sendJSON(['success'=>false,'message'=>'POST required']);
requireLogin();
$uid = (int)$_SESSION['user_id'];

if ($action === 'like') {
    $exists = $conn->query("SELECT id FROM likes WHERE user_id=$uid AND article_id=$artId")->num_rows > 0;
    if ($exists) {
        $conn->query("DELETE FROM likes WHERE user_id=$uid AND article_id=$artId");
        $liked = false;
    } else {
        $conn->query("INSERT INTO likes (user_id,article_id) VALUES ($uid,$artId)");
        $liked = true;
    }
    $count = (int)$conn->query("SELECT COUNT(*) c FROM likes WHERE article_id=$artId")->fetch_assoc()['c'];
    sendJSON(['success'=>true,'liked'=>$liked,'like_count'=>$count]);
}

if ($action === 'bookmark') {
    $exists = $conn->query("SELECT id FROM bookmarks WHERE user_id=$uid AND article_id=$artId")->num_rows > 0;
    if ($exists) {
        $conn->query("DELETE FROM bookmarks WHERE user_id=$uid AND article_id=$artId");
        $bm = false;
    } else {
        $conn->query("INSERT INTO bookmarks (user_id,article_id) VALUES ($uid,$artId)");
        $bm = true;
    }
    sendJSON(['success'=>true,'bookmarked'=>$bm]);
}

sendJSON(['success'=>false,'message'=>'Unknown action'], 400);
?>