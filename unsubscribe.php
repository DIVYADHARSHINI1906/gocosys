<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — unsubscribe.php
   Newsletter Unsubscribe Page
   URL: /gocosys/unsubscribe.php?email=user@example.com
   (or with token for secure unsubscribe)
═══════════════════════════════════════════════════ */
require_once 'db.php';

$email   = trim($_GET['email'] ?? '');
$success = false;
$message = '';
$already = false;

if ($email && filter_var($email, FILTER_VALIDATE_EMAIL)) {
    // Check if subscriber exists
    $check = $conn->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
    $check->bind_param('s', $email);
    $check->execute();
    $row = $check->get_result()->fetch_assoc();
    $check->close();

    if (!$row) {
        $message = 'This email is not found in our newsletter list.';
    } elseif ($row['status'] === 'unsubscribed') {
        $already = true;
        $success = true;
        $message = 'You have already unsubscribed from our newsletter.';
    } else {
        // Unsubscribe
        $stmt = $conn->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->close();
        $success = true;
        $message = 'Successfully unsubscribed. You will no longer receive newsletters from GOCOSYS.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form submission
    $email = trim($_POST['email'] ?? '');
    if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Please enter a valid email address.';
    } else {
        $check = $conn->prepare("SELECT id, status FROM newsletter_subscribers WHERE email = ?");
        $check->bind_param('s', $email);
        $check->execute();
        $row = $check->get_result()->fetch_assoc();
        $check->close();

        if (!$row) {
            $message = 'This email is not in our newsletter list.';
        } elseif ($row['status'] === 'unsubscribed') {
            $already = true;
            $success = true;
            $message = 'You were already unsubscribed.';
        } else {
            $stmt = $conn->prepare("UPDATE newsletter_subscribers SET status = 'unsubscribed' WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->close();
            $success = true;
            $message = 'Successfully unsubscribed. You will no longer receive newsletters.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Unsubscribe – GOCOSYS Newsletter</title>
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body {
  font-family: 'DM Sans', sans-serif;
  background: #05060f;
  color: #e2e8f0;
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 20px;
}
.card {
  width: 100%;
  max-width: 480px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 20px;
  padding: 40px 36px;
  text-align: center;
  backdrop-filter: blur(20px);
}
.logo {
  font-size: 1.4rem;
  font-weight: 900;
  color: #c8942a;
  letter-spacing: 2px;
  margin-bottom: 6px;
}
.tagline {
  font-size: .72rem;
  color: #8892a4;
  letter-spacing: 1px;
  margin-bottom: 32px;
}
.icon {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto 20px;
  font-size: 1.8rem;
}
.icon.success-icon { background: rgba(76,175,80,.12); border: 1px solid rgba(76,175,80,.25); }
.icon.error-icon   { background: rgba(239,83,80,.12);  border: 1px solid rgba(239,83,80,.25); }
.icon.neutral-icon { background: rgba(144,202,249,.08); border: 1px solid rgba(144,202,249,.2); }

h1 { font-size: 1.2rem; font-weight: 700; color: #fff; margin-bottom: 10px; }
p  { font-size: .88rem; color: #8892a4; line-height: 1.7; margin-bottom: 24px; }

.email-box {
  background: rgba(255,255,255,.04);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 10px;
  padding: 10px 14px;
  margin-bottom: 22px;
  font-size: .84rem;
  color: #c8d8e8;
}

.input {
  width: 100%;
  padding: 12px 16px;
  background: rgba(255,255,255,.05);
  border: 1px solid rgba(255,255,255,.1);
  border-radius: 12px;
  color: #e2e8f0;
  font-family: 'DM Sans', sans-serif;
  font-size: .88rem;
  outline: none;
  margin-bottom: 14px;
  transition: border .2s;
}
.input:focus { border-color: rgba(200,148,42,.4); }
.input::placeholder { color: #8892a4; }

.btn {
  width: 100%;
  padding: 13px;
  background: linear-gradient(135deg, #c8942a, #e8a730);
  border: none;
  border-radius: 12px;
  color: #000;
  font-family: 'DM Sans', sans-serif;
  font-size: .9rem;
  font-weight: 700;
  cursor: pointer;
  transition: all .3s;
}
.btn:hover { transform: translateY(-2px); box-shadow: 0 10px 30px rgba(200,148,42,.4); }

.back-link {
  display: inline-block;
  margin-top: 20px;
  color: #8892a4;
  text-decoration: none;
  font-size: .82rem;
  transition: color .2s;
}
.back-link:hover { color: #c8942a; }

.resubscribe {
  margin-top: 16px;
  font-size: .8rem;
  color: #8892a4;
}
.resubscribe a { color: #90CAF9; text-decoration: none; }
.resubscribe a:hover { color: #c8942a; }

.alert {
  padding: 12px 16px;
  border-radius: 10px;
  font-size: .84rem;
  margin-bottom: 20px;
  text-align: left;
}
.alert.err { background: rgba(239,83,80,.1); border: 1px solid rgba(239,83,80,.25); color: #ef9a9a; }
</style>
</head>
<body>
<div class="card">
  <div class="logo">GOCOSYS</div>
  <div class="tagline">Digital Excellence Platform</div>

  <?php if ($success): ?>
    <!-- Success State -->
    <div class="icon success-icon">✅</div>
    <h1><?= $already ? 'Already Unsubscribed' : 'Successfully Unsubscribed' ?></h1>
    <p><?= htmlspecialchars($message) ?></p>
    <?php if ($email): ?>
    <div class="email-box">📧 <?= htmlspecialchars($email) ?></div>
    <?php endif; ?>
    <div class="resubscribe">
      Changed your mind? <a href="blog.html">Subscribe again on our blog →</a>
    </div>
    <a href="index.html" class="back-link">← Back to GOCOSYS</a>

  <?php elseif ($email && !$success && $message): ?>
    <!-- Email not found -->
    <div class="icon error-icon">❌</div>
    <h1>Email Not Found</h1>
    <?php if ($message): ?>
    <div class="alert err"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
      <p>Try entering your email manually:</p>
      <input type="email" name="email" class="input" placeholder="Your email address" value="<?= htmlspecialchars($email) ?>">
      <button type="submit" class="btn">Unsubscribe</button>
    </form>
    <a href="index.html" class="back-link">← Back to GOCOSYS</a>

  <?php else: ?>
    <!-- Default / form state -->
    <div class="icon neutral-icon">📧</div>
    <h1>Unsubscribe from Newsletter</h1>
    <p>Enter your email address to unsubscribe from the GOCOSYS newsletter. You won't receive any more emails from us.</p>
    <?php if ($message && !$success): ?>
    <div class="alert err"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>
    <form method="POST">
      <input type="email" name="email" class="input" placeholder="Your email address" required
             value="<?= htmlspecialchars($email) ?>">
      <button type="submit" class="btn">Unsubscribe</button>
    </form>
    <a href="index.html" class="back-link">← Back to GOCOSYS</a>
  <?php endif; ?>
</div>
</body>
</html>
