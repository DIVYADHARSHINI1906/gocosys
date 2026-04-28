<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — import_articles.php
   ஒரே ஒரு முறை மட்டும் run பண்ணுங்கள்:
   http://localhost/gocosys/import_articles.php
   முடிந்தவுடன் இந்த file delete பண்ணுங்கள்!
═══════════════════════════════════════════════════ */
require_once 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Import Articles — GOCOSYS</title>
<style>
  body{font-family:sans-serif;background:#05060f;color:#e2e8f0;padding:40px;max-width:700px;margin:0 auto;}
  h1{color:#c8942a;} .btn{background:#c8942a;color:#000;border:none;padding:14px 28px;border-radius:10px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:20px;}
  .btn:hover{background:#e8a730;} .result{margin-top:20px;padding:20px;border-radius:12px;}
  .ok{background:rgba(102,187,106,.1);border:1px solid rgba(102,187,106,.3);color:#a5d6a7;}
  .err{background:rgba(239,83,80,.1);border:1px solid rgba(239,83,80,.3);color:#ef9a9a;}
  pre{background:rgba(255,255,255,.04);padding:14px;border-radius:8px;font-size:.82rem;overflow:auto;max-height:300px;}
  .warn{background:rgba(255,160,0,.1);border:1px solid rgba(255,160,0,.3);color:#ffd54f;padding:14px;border-radius:10px;margin-bottom:20px;}
</style>
</head>
<body>
<h1>📦 GOCOSYS — Import Articles to DB</h1>
<div class="warn">⚠️ இந்த page-ஐ <b>ஒரே ஒரு முறை மட்டும்</b> run பண்ணுங்கள். முடிந்தவுடன் இந்த file-ஐ delete பண்ணுங்கள்!</div>

<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $json = $_POST['articles_json'] ?? '';
    $articles = json_decode($json, true);

    if (!$articles || !is_array($articles)) {
        echo '<div class="result err">❌ Invalid JSON data. Try again.</div>';
    } else {
        $imported = 0;
        $skipped  = 0;
        $errors   = [];

        // Check if articles table already has data
        $existing = $conn->query("SELECT COUNT(*) c FROM articles")->fetch_assoc()['c'];
        if ($existing > 0) {
            echo '<div class="result err">⚠️ Articles table already has ' . $existing . ' records. Clear the table first or skip import.</div>';
        } else {
            foreach ($articles as $a) {
                $category  = $conn->real_escape_string($a['category']      ?? '');
                $cat_label = $conn->real_escape_string($a['categoryLabel'] ?? '');
                $title     = $conn->real_escape_string($a['title']         ?? '');
                $excerpt   = $conn->real_escape_string($a['excerpt']       ?? '');
                $content   = $conn->real_escape_string($a['content']       ?? '');
                $author    = $conn->real_escape_string($a['author']        ?? '');
                $a_init    = $conn->real_escape_string($a['authorInitials']?? '');
                $a_role    = $conn->real_escape_string($a['authorRole']    ?? '');
                $a_color   = $conn->real_escape_string($a['authorColor']   ?? '');
                $read_time = $conn->real_escape_string($a['readTime']      ?? '5 min');
                $featured  = empty($a['featured']) ? 0 : 1;
                $dt        = date('Y-m-d H:i:s', @strtotime($a['date'] ?? '') ?: time());

                $sql = "INSERT INTO articles
                        (category,category_label,title,excerpt,content,author,author_initials,author_role,author_color,read_time,featured,created_at)
                        VALUES
                        ('$category','$cat_label','$title','$excerpt','$content','$author','$a_init','$a_role','$a_color','$read_time',$featured,'$dt')";

                if ($conn->query($sql)) {
                    $imported++;
                } else {
                    $skipped++;
                    $errors[] = "Article '{$a['title']}': " . $conn->error;
                }
            }

            echo '<div class="result ok">';
            echo "✅ <b>$imported articles imported</b> successfully!";
            if ($skipped) echo "<br>⚠️ $skipped articles skipped.";
            echo '</div>';

            if ($errors) {
                echo '<pre>' . implode("\n", $errors) . '</pre>';
            }
        }
    }
}
?>

<p>உங்கள் <b>article_data.js</b> file-ல் உள்ள <code>ARTICLES</code> array-ஐ JSON-ஆக convert பண்ணி இங்கே paste பண்ணுங்கள்:</p>

<p style="color:#8892a4;font-size:.85rem;">
  Browser console-ல் இதை run பண்ணுங்கள் (article_data.js load ஆன page-ல்):<br>
  <code style="background:rgba(255,255,255,.08);padding:4px 10px;border-radius:6px;display:inline-block;margin-top:6px;">
    copy(JSON.stringify(ARTICLES))
  </code><br>
  பிறகு கீழே paste பண்ணுங்கள்.
</p>

<form method="POST">
  <textarea name="articles_json"
    style="width:100%;height:200px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.1);border-radius:10px;color:#e2e8f0;padding:14px;font-family:monospace;font-size:.82rem;resize:vertical;"
    placeholder='[{"id":1,"category":"ai",...}]'></textarea>
  <br>
  <button type="submit" class="btn">📥 Import to Database</button>
</form>

<div style="margin-top:40px;padding:16px;background:rgba(239,83,80,.08);border:1px solid rgba(239,83,80,.2);border-radius:10px;">
  <b style="color:#ef9a9a;">⚠️ Security Warning:</b>
  <p style="color:#8892a4;font-size:.85rem;margin-top:8px;">
    Import முடிந்தவுடன்: <code>htdocs/gocosys/import_articles.php</code> file-ஐ உடனே delete பண்ணுங்கள்!
  </p>
</div>

</body>
</html>