<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — api_workshops.php
   GET  ?action=list         → all workshops (public)
   GET  ?action=get&id=5     → single workshop
   POST ?action=create       → create (admin only)
   POST ?action=update       → update (admin only)
   POST ?action=delete       → delete (admin only)
═══════════════════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

session_start();
require_once 'db.php';

$action = $_GET['action'] ?? 'list';

/* ── LIST ── */
if ($action === 'list') {
    $stmt = $conn->prepare(
        "SELECT * FROM workshops ORDER BY workshop_date ASC"
    );
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    foreach ($rows as &$r) {
        $r['date_formatted'] = date('d M Y', strtotime($r['workshop_date']));
        $r['time_formatted'] = date('h:i A', strtotime($r['workshop_time']));
    }
    sendJSON(['success' => true, 'workshops' => $rows]);
}

/* ── GET single ── */
if ($action === 'get') {
    $id   = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM workshops WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row  = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$row) sendJSON(['success' => false, 'message' => 'Not found'], 404);
    sendJSON(['success' => true, 'workshop' => $row]);
}

/* ── CREATE ── */
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data     = json_decode(file_get_contents('php://input'), true);
    $title    = trim($data['title']        ?? '');
    $desc     = trim($data['description']  ?? '');
    $date     = trim($data['workshop_date']?? '');
    $time     = trim($data['workshop_time']?? '10:00:00');
    $mode     = trim($data['mode']         ?? 'Online');
    $price    = trim($data['price']        ?? 'Free');
    $seats    = (int)($data['seats']       ?? 0);
    $icon     = trim($data['icon']         ?? '🎓');
    $color    = trim($data['color']        ?? 'linear-gradient(135deg,#90CAF9,#1565c0)');
    $reg_link = trim($data['register_link']?? '');
    $status   = trim($data['status']       ?? 'upcoming');

    if (!$title || !$date)
        sendJSON(['success' => false, 'message' => 'Title and date required'], 400);

    $stmt = $conn->prepare(
        "INSERT INTO workshops (title,description,workshop_date,workshop_time,mode,price,seats,icon,color,register_link,status)
         VALUES (?,?,?,?,?,?,?,?,?,?,?)"
    );
    $stmt->bind_param('ssssssissss',
        $title, $desc, $date, $time, $mode, $price, $seats, $icon, $color, $reg_link, $status
    );
    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();
        sendJSON(['success' => true, 'message' => 'Workshop created!', 'id' => $newId]);
    } else {
        sendJSON(['success' => false, 'message' => 'DB error: ' . $conn->error], 500);
    }
}

/* ── UPDATE ── */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data     = json_decode(file_get_contents('php://input'), true);
    $id       = (int)($data['id']          ?? 0);
    $title    = trim($data['title']        ?? '');
    $desc     = trim($data['description']  ?? '');
    $date     = trim($data['workshop_date']?? '');
    $time     = trim($data['workshop_time']?? '10:00:00');
    $mode     = trim($data['mode']         ?? 'Online');
    $price    = trim($data['price']        ?? 'Free');
    $seats    = (int)($data['seats']       ?? 0);
    $icon     = trim($data['icon']         ?? '🎓');
    $color    = trim($data['color']        ?? 'linear-gradient(135deg,#90CAF9,#1565c0)');
    $reg_link = trim($data['register_link']?? '');
    $status   = trim($data['status']       ?? 'upcoming');

    if (!$id || !$title || !$date)
        sendJSON(['success' => false, 'message' => 'ID, title and date required'], 400);

    $stmt = $conn->prepare(
        "UPDATE workshops SET title=?,description=?,workshop_date=?,workshop_time=?,
         mode=?,price=?,seats=?,icon=?,color=?,register_link=?,status=? WHERE id=?"
    );
    $stmt->bind_param('ssssssissssi',
        $title, $desc, $date, $time, $mode, $price, $seats, $icon, $color, $reg_link, $status, $id
    );
    if ($stmt->execute()) {
        $stmt->close();
        sendJSON(['success' => true, 'message' => 'Workshop updated!']);
    } else {
        sendJSON(['success' => false, 'message' => 'DB error: ' . $conn->error], 500);
    }
}

/* ── DELETE ── */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);
    if (!$id) sendJSON(['success' => false, 'message' => 'ID required'], 400);

    $stmt = $conn->prepare("DELETE FROM workshops WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        sendJSON(['success' => true, 'message' => 'Workshop deleted']);
    } else {
        sendJSON(['success' => false, 'message' => 'Not found'], 404);
    }
}

sendJSON(['success' => false, 'message' => 'Unknown action'], 400);
