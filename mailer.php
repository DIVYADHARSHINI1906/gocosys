<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — mailer.php
   PHPMailer + Gmail SMTP Configuration
   
   SETUP STEPS:
   1. Download PHPMailer:
      composer require phpmailer/phpmailer
      OR download ZIP from: https://github.com/PHPMailer/PHPMailer
      Place in: htdocs/gocosys/PHPMailer/
   
   2. Gmail App Password:
      - Gmail → Settings → Security
      - 2-Step Verification: ON ஆக்குங்கள்
      - "App passwords" → "Mail" → Generate
      - 16-digit password கிடைக்கும் → கீழே paste பண்ணுங்கள்
   
   3. இந்த file-ல் YOUR_EMAIL மற்றும் YOUR_APP_PASSWORD மாற்றுங்கள்
═══════════════════════════════════════════════════ */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// PHPMailer autoload — path adjust பண்ணுங்கள் if needed
require_once __DIR__ . '/PHPMailer/src/Exception.php';
require_once __DIR__ . '/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/PHPMailer/src/SMTP.php';

/* ── Gmail SMTP Credentials ── */
define('SMTP_HOST',     'smtp.gmail.com');
define('SMTP_PORT',     587);
define('SMTP_USERNAME', 'divyajes1004@gmail.com');   // உங்கள் Gmail
define('SMTP_PASSWORD', 'xvdx adah mlen mhdv');  // 16-digit App Password
define('SMTP_FROM',     'divyajes1004@gmail.com');   // From email
define('SMTP_FROM_NAME','GOCOSYS Team');             // From name

/**
 * Send HTML Email via PHPMailer + Gmail SMTP
 *
 * @param string $toEmail    Recipient email
 * @param string $toName     Recipient name
 * @param string $subject    Email subject
 * @param string $htmlBody   Full HTML content (from buildEmailHTML helper)
 * @return bool              true = sent, false = failed
 */
function gocosysSendMail(string $toEmail, string $toName, string $subject, string $htmlBody): bool {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Disable SSL verification for localhost (development only)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true,
            ],
        ];

        // From
        $mail->setFrom(SMTP_FROM, SMTP_FROM_NAME);
        $mail->addReplyTo(SMTP_FROM, SMTP_FROM_NAME);

        // To
        $mail->addAddress($toEmail, $toName);

        // Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(['<br>', '<br/>', '</p>', '</div>'], "\n", $htmlBody));

        $mail->send();
        return true;

    } catch (Exception $e) {
        // Log error silently (don't expose to user)
        error_log('GOCOSYS Mailer Error: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Standard GOCOSYS HTML Email Wrapper
 * All emails use this template for consistent styling
 */
function gocosysBuildEmail(string $bodyHTML, string $siteUrl = 'http://localhost/gocosys'): string {
    return '<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
body{margin:0;padding:0;background:#f4f6f9;font-family:Arial,sans-serif;}
.wrap{max-width:600px;margin:32px auto;background:#fff;border-radius:16px;overflow:hidden;box-shadow:0 4px 24px rgba(0,0,0,.08);}
.hdr{background:linear-gradient(135deg,#05060f 0%,#1a1c2e 100%);padding:32px 40px;text-align:center;}
.logo{font-size:1.6rem;font-weight:900;color:#c8942a;letter-spacing:2px;}
.tagline{font-size:.75rem;color:#8892a4;margin-top:4px;letter-spacing:1px;}
.bar{height:3px;background:linear-gradient(90deg,#c8942a,#90CAF9,#c8942a);}
.body{padding:36px 40px;}
.title{font-size:1.2rem;font-weight:700;color:#1a1c2e;margin-bottom:8px;}
.sub{font-size:.88rem;color:#64748b;margin-bottom:24px;line-height:1.6;}
.info-card{background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:18px 22px;margin-bottom:18px;}
.irow{display:flex;gap:12px;padding:7px 0;border-bottom:1px solid #e2e8f0;font-size:.87rem;}
.irow:last-child{border-bottom:none;}
.lbl{font-weight:700;color:#475569;min-width:100px;}
.val{color:#1e293b;flex:1;}
.msg-box{background:#f8fafc;border-left:3px solid #c8942a;padding:14px 18px;margin:18px 0;font-size:.87rem;color:#334155;line-height:1.75;white-space:pre-wrap;}
.btn{display:inline-block;background:linear-gradient(135deg,#c8942a,#e8a730);color:#000;text-decoration:none;font-weight:700;font-size:.86rem;padding:13px 28px;border-radius:50px;margin-top:8px;}
.divider{height:1px;background:#e2e8f0;margin:22px 0;}
.footer{background:#f8fafc;padding:20px 40px;text-align:center;font-size:.73rem;color:#94a3b8;line-height:1.7;}
.footer a{color:#c8942a;text-decoration:none;}
@media(max-width:480px){.body{padding:24px 20px!important;}.hdr{padding:24px 20px!important;}.footer{padding:16px 20px!important;}}
</style>
</head>
<body>
<div class="wrap">
  <div class="hdr">
    <div class="logo">GOCOSYS</div>
    <div class="tagline">Digital Excellence Platform</div>
  </div>
  <div class="bar"></div>
  <div class="body">' . $bodyHTML . '</div>
  <div class="footer">
    &copy; ' . date('Y') . ' <a href="' . $siteUrl . '">GOCOSYS</a> &middot; Chennai, Tamil Nadu, India<br>
    <small>Automated email — do not reply directly.</small>
  </div>
</div>
</body>
</html>';
}

/**
 * Log email to DB
 */
function gocosysLogEmail(mysqli $conn, string $toEmail, string $subject, string $type, bool $sent): void {
    try {
        $e  = $conn->real_escape_string(substr($toEmail, 0, 149));
        $s  = $conn->real_escape_string(substr($subject, 0, 299));
        $t  = $conn->real_escape_string($type);
        $ok = $sent ? 1 : 0;
        @$conn->query("INSERT INTO email_logs (to_email,subject,type,sent,created_at) VALUES ('$e','$s','$t',$ok,NOW())");
    } catch (Exception $e) { /* silent */ }
}
