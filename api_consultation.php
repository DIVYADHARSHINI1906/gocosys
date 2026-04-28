<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — api_consultation.php  (PHPMailer Version)
   POST ?action=book    → submit booking + email notify
   GET  ?action=list    → list bookings (admin only)
   POST ?action=status  → update status (admin only)
   
   FIX: Original file had "<?php\" backslash syntax error
   FIX: php mail() replaced with PHPMailer gocosysSendMail()
═══════════════════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

session_start();
require_once 'db.php';
require_once 'mailer.php';   // PHPMailer helper

define('CONSULT_SITE_URL',  'http://localhost/gocosys');
define('CONSULT_ADMIN_EMAIL','dharshu0046@gmail.com');
define('CONSULT_ADMIN_NAME', 'Dharshu');

$action = $_GET['action'] ?? 'book';

/* ══════════════════════════════════════════════════
   BOOK — Public form submission
══════════════════════════════════════════════════ */
if ($action === 'book' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $data    = json_decode(file_get_contents('php://input'), true);
    $name    = trim($data['name']         ?? '');
    $email   = trim($data['email']        ?? '');
    $phone   = trim($data['phone']        ?? '');
    $service = trim($data['service_type'] ?? '');
    $message = trim($data['message']      ?? '');

    // Validation
    if (!$name)
        sendJSON(['success' => false, 'message' => 'Name required'], 400);
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
        sendJSON(['success' => false, 'message' => 'Enter a valid email address'], 400);
    if (!$service)
        sendJSON(['success' => false, 'message' => 'Please select an area of interest'], 400);
    if (strlen($message) < 20)
        sendJSON(['success' => false, 'message' => 'Please provide more details about your goals (min 20 chars)'], 400);

    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    // Rate limit: 3 per day per email
    $rateStmt = $conn->prepare("SELECT COUNT(*) c FROM consultations WHERE email=? AND created_at > NOW() - INTERVAL 1 DAY");
    $rateStmt->bind_param('s', $email);
    $rateStmt->execute();
    $rateCount = (int)$rateStmt->get_result()->fetch_assoc()['c'];
    $rateStmt->close();
    if ($rateCount >= 3)
        sendJSON(['success' => false, 'message' => 'You have already submitted 3 bookings today. Please try again tomorrow.'], 429);

    // Insert booking
    $stmt = $conn->prepare("INSERT INTO consultations (name,email,phone,service_type,project_details,ip_address) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param('ssssss', $name, $email, $phone, $service, $message, $ip);
    if (!$stmt->execute())
        sendJSON(['success' => false, 'message' => 'Database error: ' . $conn->error], 500);
    $insertId = $conn->insert_id;
    $stmt->close();

    /* ── Email 1: User Confirmation ── */
    $uSubject = 'Booking Confirmed – GOCOSYS #' . $insertId;
    $uBody    = gocosysBuildEmail('
      <div class="title">Booking Confirmed, ' . htmlspecialchars($name, ENT_QUOTES) . '! 🎉</div>
      <p class="sub">Your consultation request has been received. We will contact you within 24 hours.</p>
      <div class="info-card">
        <div class="irow"><span class="lbl">Booking ID</span><span class="val">#' . $insertId . '</span></div>
        <div class="irow"><span class="lbl">Name</span><span class="val">' . htmlspecialchars($name, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Service</span><span class="val">' . htmlspecialchars($service, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Status</span><span class="val">⏳ Pending Review</span></div>
        <div class="irow"><span class="lbl">Date</span><span class="val">' . date('d M Y, h:i A') . '</span></div>
      </div>
      <div class="msg-box"><strong>Your Goals:</strong>
' . htmlspecialchars($message, ENT_QUOTES) . '</div>
      <div class="divider"></div>
      <p style="font-size:.84rem;color:#64748b;">
        📞 Quick contact: <strong>+91 63793 58673</strong><br>
        📧 Reply to this email for any queries.
      </p>
      <a href="' . CONSULT_SITE_URL . '/blog.html" class="btn">Explore Our Blog →</a>
    ', CONSULT_SITE_URL);
    $sentU = gocosysSendMail($email, $name, $uSubject, $uBody);
    gocosysLogEmail($conn, $email, $uSubject, 'consultation_confirm', $sentU);

    /* ── Email 2: Admin Notification ── */
    $aSubject = 'New Consultation Booking #' . $insertId . ' – ' . $name;
    $aBody    = gocosysBuildEmail('
      <div class="title">New Consultation Request 📋</div>
      <p class="sub">A new booking has been submitted. Please review and respond.</p>
      <div class="info-card">
        <div class="irow"><span class="lbl">Booking ID</span><span class="val">#' . $insertId . '</span></div>
        <div class="irow"><span class="lbl">Name</span><span class="val">' . htmlspecialchars($name, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Email</span><span class="val">' . htmlspecialchars($email, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Phone</span><span class="val">' . htmlspecialchars($phone ?: 'Not provided', ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Service</span><span class="val">' . htmlspecialchars($service, ENT_QUOTES) . '</span></div>
        <div class="irow"><span class="lbl">Submitted</span><span class="val">' . date('d M Y, h:i A') . '</span></div>
      </div>
      <div class="msg-box"><strong>Client Goals:</strong>
' . htmlspecialchars($message, ENT_QUOTES) . '</div>
      <a href="' . CONSULT_SITE_URL . '/admin.html" class="btn">Open Admin Panel →</a>
    ', CONSULT_SITE_URL);
    $sentA = gocosysSendMail(CONSULT_ADMIN_EMAIL, CONSULT_ADMIN_NAME, $aSubject, $aBody);
    gocosysLogEmail($conn, CONSULT_ADMIN_EMAIL, $aSubject, 'consultation_admin_notify', $sentA);

    sendJSON([
        'success' => true,
        'message' => 'Thank you, ' . $name . '! Your booking is confirmed. A confirmation email has been sent.',
        'id'      => $insertId,
        'email_sent' => $sentU
    ]);
}

/* ══════════════════════════════════════════════════
   LIST — Admin only
══════════════════════════════════════════════════ */
if ($action === 'list') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $limit  = (int)($_GET['limit']  ?? 50);
    $offset = (int)($_GET['offset'] ?? 0);
    $status = $_GET['status'] ?? '';

    $where = []; $params = []; $types = '';
    if ($status) { $where[] = 'status=?'; $params[] = $status; $types .= 's'; }
    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $cntStmt = $conn->prepare("SELECT COUNT(*) c FROM consultations $whereSQL");
    if ($types) $cntStmt->bind_param($types, ...$params);
    $cntStmt->execute();
    $total = (int)$cntStmt->get_result()->fetch_assoc()['c'];
    $cntStmt->close();

    $allP = array_merge($params, [$limit, $offset]);
    $stmt = $conn->prepare(
        "SELECT id,name,email,phone,service_type,LEFT(project_details,150) as details,status,created_at
         FROM consultations $whereSQL ORDER BY created_at DESC LIMIT ? OFFSET ?"
    );
    $stmt->bind_param($types . 'ii', ...$allP);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as &$r)
        $r['booked_on'] = date('d M Y, h:i A', strtotime($r['created_at']));

    sendJSON(['success' => true, 'consultations' => $rows, 'total' => $total]);
}

/* ══════════════════════════════════════════════════
   STATUS UPDATE — Admin only
══════════════════════════════════════════════════ */
if ($action === 'status' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data    = json_decode(file_get_contents('php://input'), true);
    $id      = (int)($data['id']     ?? 0);
    $status  = $data['status']        ?? '';
    $allowed = ['pending', 'confirmed', 'cancelled', 'completed'];

    if (!$id || !in_array($status, $allowed))
        sendJSON(['success' => false, 'message' => 'Invalid id or status'], 400);

    $stmt = $conn->prepare("UPDATE consultations SET status=? WHERE id=?");
    $stmt->bind_param('si', $status, $id);
    $stmt->execute();
    $stmt->close();

    // Send status update email to client
    if (in_array($status, ['confirmed', 'cancelled', 'completed'])) {
        $info = $conn->query("SELECT name,email,service_type FROM consultations WHERE id=$id")->fetch_assoc();
        if ($info) {
            $labels = [
                'confirmed' => '✅ Booking Confirmed!',
                'cancelled' => '❌ Booking Cancelled',
                'completed' => '🎉 Consultation Completed'
            ];
            $msgs = [
                'confirmed' => 'Your consultation has been confirmed! We will call you soon to discuss the details.',
                'cancelled' => 'Your booking has been cancelled. Please contact us to re-book at a convenient time.',
                'completed' => 'Thank you for your consultation with GOCOSYS! We hope it was valuable.'
            ];

            $sSub  = $labels[$status] . ' – GOCOSYS Booking #' . $id;
            $sBody = gocosysBuildEmail('
              <div class="title">' . $labels[$status] . '</div>
              <p class="sub">' . $msgs[$status] . '</p>
              <div class="info-card">
                <div class="irow"><span class="lbl">Booking ID</span><span class="val">#' . $id . '</span></div>
                <div class="irow"><span class="lbl">Name</span><span class="val">' . htmlspecialchars($info['name'], ENT_QUOTES) . '</span></div>
                <div class="irow"><span class="lbl">Service</span><span class="val">' . htmlspecialchars($info['service_type'], ENT_QUOTES) . '</span></div>
                <div class="irow"><span class="lbl">Status</span><span class="val">' . strtoupper($status) . '</span></div>
              </div>
              <a href="' . CONSULT_SITE_URL . '" class="btn">Visit GOCOSYS →</a>
            ', CONSULT_SITE_URL);

            $sent = gocosysSendMail($info['email'], $info['name'], $sSub, $sBody);
            gocosysLogEmail($conn, $info['email'], $sSub, 'consultation_status_' . $status, $sent);
        }
    }

    sendJSON(['success' => true, 'message' => 'Status updated to ' . $status]);
}

sendJSON(['success' => false, 'message' => 'Unknown action'], 400);
