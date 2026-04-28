<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — api_newsletter.php  (PHPMailer Version)
   POST → subscribe email + send welcome email + save to DB
   FIX: php mail() → PHPMailer gocosysSendMail()
═══════════════════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

require_once 'db.php';
require_once 'mailer.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST')
    sendJSON(['success' => false, 'message' => 'POST required']);

$data  = json_decode(file_get_contents('php://input'), true);
$email = trim($data['email'] ?? '');

if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL))
    sendJSON(['success' => false, 'message' => 'Please enter a valid email address']);

define('NL_SITE_URL',  'http://localhost/gocosys');
define('NL_BLOG_URL',  'http://localhost/gocosys/blog.html');

/* ── Check if already subscribed ── */
$check = $conn->prepare("SELECT id, status FROM newsletter_subscribers WHERE email=?");
$check->bind_param('s', $email);
$check->execute();
$existing = $check->get_result()->fetch_assoc();
$check->close();

if ($existing) {
    if ($existing['status'] === 'active')
        sendJSON(['success' => false, 'message' => 'This email is already subscribed to our newsletter!']);

    // Re-activate unsubscribed email
    $conn->query("UPDATE newsletter_subscribers SET status='active', subscribed_at=NOW() WHERE email='" . $conn->real_escape_string($email) . "'");
} else {
    // New subscriber
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $stmt = $conn->prepare("INSERT INTO newsletter_subscribers (email, ip_address, status, subscribed_at) VALUES (?,?,'active',NOW())");
    $stmt->bind_param('ss', $email, $ip);
    if (!$stmt->execute())
        sendJSON(['success' => false, 'message' => 'Subscription failed. Please try again.']);
    $stmt->close();
}

/* ── Welcome Email ── */
$subject = 'Welcome to GOCOSYS Newsletter!';
$body    = gocosysBuildEmail('
  <div class="title">Welcome to GOCOSYS! 🎉</div>
  <p class="sub">
    Thank you! <strong>' . htmlspecialchars($email, ENT_QUOTES) . '</strong> has been successfully subscribed.<br>
    We will send you the latest articles, workshops, and career tips directly to your inbox.
  </p>

  <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin:24px 0;">
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
      <div style="font-size:1.3rem;margin-bottom:6px;">📝</div>
      <div style="font-size:.84rem;font-weight:700;color:#1e293b;margin-bottom:4px;">Expert Articles</div>
      <div style="font-size:.76rem;color:#64748b;line-height:1.5;">Web Dev, AI, SEO & Marketing insights every week.</div>
    </div>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
      <div style="font-size:1.3rem;margin-bottom:6px;">🎓</div>
      <div style="font-size:.84rem;font-weight:700;color:#1e293b;margin-bottom:4px;">Live Workshops</div>
      <div style="font-size:.76rem;color:#64748b;line-height:1.5;">Hands-on sessions with industry experts.</div>
    </div>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
      <div style="font-size:1.3rem;margin-bottom:6px;">💼</div>
      <div style="font-size:.84rem;font-weight:700;color:#1e293b;margin-bottom:4px;">Career Tips</div>
      <div style="font-size:.76rem;color:#64748b;line-height:1.5;">Placement strategies & interview guidance.</div>
    </div>
    <div style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:16px;">
      <div style="font-size:1.3rem;margin-bottom:6px;">🤖</div>
      <div style="font-size:.84rem;font-weight:700;color:#1e293b;margin-bottom:4px;">AI & Tech News</div>
      <div style="font-size:.76rem;color:#64748b;line-height:1.5;">Stay ahead with the latest in AI & technology.</div>
    </div>
  </div>

  <div class="divider"></div>
  <p style="font-size:.86rem;color:#64748b;text-align:center;margin-bottom:16px;">
    Our latest articles are live — explore them now!
  </p>
  <div style="text-align:center;">
    <a href="' . NL_BLOG_URL . '" class="btn">Read Latest Articles →</a>
  </div>
', NL_SITE_URL);

$sent = gocosysSendMail($email, '', $subject, $body);
gocosysLogEmail($conn, $email, $subject, 'newsletter_welcome', $sent);

sendJSON([
    'success'    => true,
    'message'    => 'Successfully subscribed! Welcome email has been sent. 📩',
    'email_sent' => $sent
]);
