<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — api_contact.php  (PHPMailer Version)
   POST ?action=submit  → submit contact + send emails
   GET  ?action=list    → list messages (admin only)
   GET  ?action=get&id= → single message (admin only)
   POST ?action=status  → update status (admin only)
   FIX: php mail() → PHPMailer gocosysSendMail()
═══════════════════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

session_start();
require_once 'db.php';
require_once 'mailer.php';

define('CT_ADMIN_EMAIL', 'dharshu0046@gmail.com');
define('CT_ADMIN_NAME',  'Dharshu');
define('CT_SITE_URL',    'http://localhost/gocosys');

$action = $_GET['action'] ?? 'submit';

/* ══════════════════════════════════════════════════
   SUBMIT — Public contact form
══════════════════════════════════════════════════ */
if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data    = json_decode(file_get_contents('php://input'), true);
    $name    = trim($data['name']    ?? '');
    $email   = trim($data['email']   ?? '');
    $subject = trim($data['subject'] ?? '');
    $message = trim($data['message'] ?? '');

    if (!$name)
        sendJSON(['success' => false, 'message' => 'Name is required'], 400);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
        sendJSON(['success' => false, 'message' => 'Please enter a valid email address'], 400);
    if (!$subject)
        sendJSON(['success' => false, 'message' => 'Subject is required'], 400);
    if (strlen($message) < 10)
        sendJSON(['success' => false, 'message' => 'Message must be at least 10 characters'], 400);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Rate limit: 3 per hour
    $rc = $conn->prepare("SELECT COUNT(*) c FROM contact_messages WHERE (ip_address=? OR email=?) AND created_at > NOW() - INTERVAL 1 HOUR");
    $rc->bind_param('ss', $ip, $email);
    $rc->execute();
    if ((int)$rc->get_result()->fetch_assoc()['c'] >= 3) {
        $rc->close();
        sendJSON(['success' => false, 'message' => 'Too many messages submitted. Please wait 1 hour before trying again.'], 429);
    }
    $rc->close();

    $stmt = $conn->prepare("INSERT INTO contact_messages (name,email,subject,message,ip_address) VALUES (?,?,?,?,?)");
    $stmt->bind_param('sssss', $name, $email, $subject, $message, $ip);
    if (!$stmt->execute())
        sendJSON(['success' => false, 'message' => 'Database error: ' . $conn->error], 500);
    $insertId = $conn->insert_id;
    $stmt->close();

    /* ── Email 1: Auto-reply to user ── */
    $userSubject = 'Message Received – GOCOSYS';
    $userBody    = gocosysBuildEmail('
      <div class="title">We received your message, ' . htmlspecialchars($name, ENT_QUOTES) . '! ✅</div>
      <p class="sub">Thank you for reaching out! We will reply within 24 hours.</p>
      <div class="info-card">
        <div class="irow"><span class="lbl">Message ID</span><span class="val">#' . $insertId . '</span></div>
        <div class="irow"><span class="lbl">Name</span><span class="val">' . htmlspecialchars($name, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Subject</span><span class="val">' . htmlspecialchars($subject, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Sent on</span><span class="val">' . date('d M Y, h:i A') . '</span></div>
      </div>
      <div class="msg-box"><strong>Your Message:</strong>
' . htmlspecialchars($message, ENT_QUOTES) . '</div>
      <div class="divider"></div>
      <p style="font-size:.84rem;color:#64748b;line-height:1.7;">
        📞 Quick contact: <strong>+91 63793 58673</strong><br>
        🌐 Visit: <a href="' . CT_SITE_URL . '" style="color:#c8942a;">' . CT_SITE_URL . '</a>
      </p>
      <a href="' . CT_SITE_URL . '/blog.html" class="btn">Explore Our Blog →</a>
    ', CT_SITE_URL);

    $sentUser = gocosysSendMail($email, $name, $userSubject, $userBody);
    gocosysLogEmail($conn, $email, $userSubject, 'contact_autoreply', $sentUser);

    /* ── Email 2: Admin Notification ── */
    $adminSubject = 'New Contact Message #' . $insertId . ' – ' . $name;
    $adminBody    = gocosysBuildEmail('
      <div class="title">New Contact Message 📬</div>
      <p class="sub">A new contact form submission has been received.</p>
      <div class="info-card">
        <div class="irow"><span class="lbl">ID</span><span class="val">#' . $insertId . '</span></div>
        <div class="irow"><span class="lbl">Name</span><span class="val">' . htmlspecialchars($name, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Email</span><span class="val">' . htmlspecialchars($email, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Subject</span><span class="val">' . htmlspecialchars($subject, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">IP</span><span class="val">' . htmlspecialchars($ip, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Received</span><span class="val">' . date('d M Y, h:i A') . '</span></div>
      </div>
      <div class="msg-box"><strong>Message:</strong>
' . htmlspecialchars($message, ENT_QUOTES) . '</div>
      <a href="' . CT_SITE_URL . '/admin.html" class="btn">Open Admin Panel →</a>
    ', CT_SITE_URL);

    $sentAdmin = gocosysSendMail(CT_ADMIN_EMAIL, CT_ADMIN_NAME, $adminSubject, $adminBody);
    gocosysLogEmail($conn, CT_ADMIN_EMAIL, $adminSubject, 'contact_admin_notify', $sentAdmin);

    sendJSON([
        'success'    => true,
        'message'    => 'Thank you, ' . $name . '! Your message has been received. We will reply within 24 hours.',
        'id'         => $insertId,
        'email_sent' => $sentUser
    ]);
}

/* ══════════════════════════════════════════════════
   LIST — Admin only
══════════════════════════════════════════════════ */
if ($action === 'list') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $status = $_GET['status'] ?? '';
    $limit  = (int)($_GET['limit']  ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);

    $where = []; $params = []; $types = '';
    if ($status) { $where[] = 'status=?'; $params[] = $status; $types .= 's'; }
    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $cntStmt = $conn->prepare("SELECT COUNT(*) c FROM contact_messages $whereSQL");
    if ($types) $cntStmt->bind_param($types, ...$params);
    $cntStmt->execute();
    $total = (int)$cntStmt->get_result()->fetch_assoc()['c'];
    $cntStmt->close();

    $allParams = array_merge($params, [$limit, $offset]);
    $stmt = $conn->prepare(
        "SELECT id,name,email,subject,LEFT(message,100) as message_preview,status,ip_address,created_at
         FROM contact_messages $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?"
    );
    $stmt->bind_param($types . 'ii', ...$allParams);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as &$r)
        $r['date'] = date('d M Y, h:i A', strtotime($r['created_at']));

    sendJSON(['success' => true, 'messages' => $rows, 'total' => $total]);
}

/* ══════════════════════════════════════════════════
   GET single — Admin only
══════════════════════════════════════════════════ */
if ($action === 'get') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $id   = (int)($_GET['id'] ?? 0);
    $stmt = $conn->prepare("SELECT * FROM contact_messages WHERE id=?");
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row  = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) sendJSON(['success' => false, 'message' => 'Message not found'], 404);
    $row['date'] = date('d M Y, h:i A', strtotime($row['created_at']));

    // Auto mark as read
    $conn->query("UPDATE contact_messages SET status='read' WHERE id=$id AND status='new'");
    sendJSON(['success' => true, 'message' => $row]);
}

/* ══════════════════════════════════════════════════
   STATUS UPDATE — Admin only
══════════════════════════════════════════════════ */
if ($action === 'status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($data['id']    ?? 0);
    $status = $data['status']       ?? '';

    if (!$id || !in_array($status, ['new', 'read', 'replied']))
        sendJSON(['success' => false, 'message' => 'Invalid ID or status'], 400);

    $stmt = $conn->prepare("UPDATE contact_messages SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();

    sendJSON(['success' => true, 'message' => 'Status updated to ' . $status]);
}

sendJSON(['success' => false, 'message' => 'Unknown action'], 400);
