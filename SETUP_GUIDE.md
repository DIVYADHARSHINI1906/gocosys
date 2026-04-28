# GOCOSYS — Complete Backend Setup Guide
## உங்கள் Project Complete ஆக எப்படி Setup பண்றது

---

## 📁 Files You Received (இந்த files replace பண்ணுங்கள்)

| File | Action | Reason |
|------|--------|--------|
| `mailer.php` | **NEW — Create** | PHPMailer SMTP config — most important! |
| `api_consultation.php` | **REPLACE** | Old file-ல் backslash syntax error + php mail() |
| `api_contact.php` | **REPLACE** | php mail() → PHPMailer |
| `api_newsletter.php` | **REPLACE** | php mail() → PHPMailer |
| `unsubscribe.php` | **NEW — Create** | Email template-ல் link இருந்தது, file missing |

---

## STEP 1 — PHPMailer Download (⭐ First do this)

### Option A: Composer (Recommended)
```bash
cd C:/xampp/htdocs/gocosys
composer require phpmailer/phpmailer
```

### Option B: Manual Download (Composer இல்லன்னா)
1. Go to: https://github.com/PHPMailer/PHPMailer
2. Click "Code" → "Download ZIP"
3. Extract and rename folder to `PHPMailer`
4. Place it in: `C:/xampp/htdocs/gocosys/PHPMailer/`

**Folder structure should be:**
```
gocosys/
├── PHPMailer/
│   └── src/
│       ├── Exception.php
│       ├── PHPMailer.php
│       └── SMTP.php
├── mailer.php      ← NEW file
├── db.php
├── login.php
└── ...
```

---

## STEP 2 — Gmail App Password (⭐ Most Critical)

1. Go to: **myaccount.google.com**
2. Click **"Security"** (left sidebar)
3. Enable **"2-Step Verification"** (must be ON)
4. Search for **"App passwords"** → click it
5. Select: App = **"Mail"** → Device = **"Other"** → type "GOCOSYS"
6. Click **"Generate"**
7. You get a **16-digit password** like: `abcd efgh ijkl mnop`

---

## STEP 3 — Edit mailer.php

Open `mailer.php` and change line:
```php
define('SMTP_PASSWORD', 'YOUR_APP_PASSWORD_HERE');
```
to your actual app password:
```php
define('SMTP_PASSWORD', 'abcdefghijklmnop');  // no spaces
```

Also verify your Gmail:
```php
define('SMTP_USERNAME', 'dharshu0046@gmail.com');
define('SMTP_FROM',     'dharshu0046@gmail.com');
```

---

## STEP 4 — Copy All Files to XAMPP

Copy these files to `C:/xampp/htdocs/gocosys/`:
```
mailer.php          → NEW file (required)
api_consultation.php → REPLACE old file
api_contact.php     → REPLACE old file
api_newsletter.php  → REPLACE old file
unsubscribe.php     → NEW file (required)
```

---

## STEP 5 — Database Setup

1. Start XAMPP (Apache + MySQL)
2. Open: http://localhost/phpmyadmin
3. Click **"SQL"** tab
4. Copy-paste content of `gocosys_setup.sql` → Click **"Go"**
5. Then run `gocosys_consulation_v2.sql` (if consultations table needs update)

---

## STEP 6 — Make Yourself Admin

After registering on the site:
```sql
UPDATE users SET role='admin' WHERE email='dharshu0046@gmail.com';
```
Run this in phpMyAdmin → SQL tab.

---

## STEP 7 — Import Articles

1. Copy `article_data.js` content to clipboard
2. Open: http://localhost/gocosys/import_articles.php
3. In browser console (on a page that loads article_data.js):
   ```javascript
   copy(JSON.stringify(ARTICLES))
   ```
4. Paste in the textarea → Click Import
5. **DELETE `import_articles.php` after importing!**

---

## STEP 8 — Test Everything

### Test Email:
1. Go to: http://localhost/gocosys/index.html
2. Fill the "Book Free Consultation" form
3. Submit → Check your Gmail inbox

### Test Login:
1. Go to: http://localhost/gocosys/blog.html
2. Click "Login" → Register new account
3. Check navbar shows your name

### Test Admin:
1. After making yourself admin (Step 6)
2. Go to: http://localhost/gocosys/admin.html
3. Should show dashboard with all data

---

## ⚠️ Common Errors & Fixes

### "SMTP connect() failed"
- Check App Password is correct
- Check Gmail 2FA is enabled
- Try: `$mail->SMTPDebug = SMTP::DEBUG_SERVER;` in mailer.php temporarily

### "Access denied for user 'root'"
- XAMPP MySQL password empty-ஆ இருக்கணும்
- `db.php` check: `define('DB_PASS', '');`

### "Class not found PHPMailer"
- PHPMailer folder path check பண்ணுங்கள்
- `mailer.php` line 11-13 require_once paths correct-ஆ இருக்கணும்

### Like/Bookmark not working
- User login required
- `api_like_bookmark.php` correct-ஆ இருக்கு — no changes needed
- `like_bookmark.php` empty file-ஐ ignore பண்ணுங்கள் (not used)

---

## 📊 Final File Structure

```
gocosys/
├── PHPMailer/src/          ← Download and place here
├── db.php                  ✅ existing
├── mailer.php              ✅ NEW — created for you
├── login.php               ✅ existing
├── register.php            ✅ existing
├── logout.php              ✅ existing
├── check_session.php       ✅ existing
├── api_articles.php        ✅ existing (no changes needed)
├── api_consultation.php    ✅ REPLACED — PHPMailer version
├── api_contact.php         ✅ REPLACED — PHPMailer version
├── api_newsletter.php      ✅ REPLACED — PHPMailer version
├── api_like_bookmark.php   ✅ existing (no changes needed)
├── unsubscribe.php         ✅ NEW — created for you
├── import_articles.php     ⚠️ DELETE after importing articles
├── gocosys_setup.sql       ✅ run in phpMyAdmin once
├── index.html
├── blog.html
├── article.html
├── admin.html
└── ...
```

---

## ✅ What Works After Setup

| Feature | Status |
|---------|--------|
| User Register/Login/Logout | ✅ |
| Admin Panel | ✅ |
| Blog Articles (from DB) | ✅ |
| Like / Bookmark articles | ✅ (login required) |
| Contact Form + Email | ✅ |
| Consultation Booking + Email | ✅ |
| Newsletter Subscribe + Welcome Email | ✅ |
| Newsletter Unsubscribe Page | ✅ |
| Email Logs in Admin | ✅ |
| Admin manage bookings/contacts | ✅ |

---

*Questions? All API endpoints return JSON with `success: true/false` and `message` field for easy debugging.*
