<?php
/* ═══════════════════════════════════════════════════
   GOCOSYS — api_articles.php
   GET  ?action=list              → all articles (no content)
   GET  ?action=get&id=5          → single article with content
   POST ?action=create            → create new article (admin only)
   POST ?action=update            → update article   (admin only)
   POST ?action=delete            → delete article   (admin only)
   POST ?action=import            → bulk import from JS (admin only, one-time)
═══════════════════════════════════════════════════ */
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') exit(0);

session_start();
require_once 'db.php';

$action = $_GET['action'] ?? 'list';

/* ── LIST all articles ─────────────────────────── */
if ($action === 'list') {
    $category = $_GET['category'] ?? '';
    $search   = $_GET['search']   ?? '';
    $limit    = (int)($_GET['limit']  ?? 100);
    $offset   = (int)($_GET['offset'] ?? 0);

    $where  = ['a.published = 1'];
    $params = [];
    $types  = '';

    if ($category && $category !== 'all') {
        $where[]  = 'a.category = ?';
        $params[] = $category;
        $types   .= 's';
    }
    if ($search) {
        $like     = '%' . $search . '%';
        $where[]  = '(a.title LIKE ? OR a.excerpt LIKE ? OR a.category_label LIKE ?)';
        $params[] = $like; $params[] = $like; $params[] = $like;
        $types   .= 'sss';
    }

    $whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $cntStmt = $conn->prepare("SELECT COUNT(*) as total FROM articles a $whereSQL");
    if ($types) $cntStmt->bind_param($types, ...$params);
    $cntStmt->execute();
    $total = $cntStmt->get_result()->fetch_assoc()['total'];
    $cntStmt->close();

    $sql = "SELECT a.id, a.category, a.category_label, a.title, a.excerpt,
                   a.author, a.author_initials, a.author_role, a.author_color,
                   a.read_time, a.featured, a.created_at,
                   (SELECT COUNT(*) FROM likes    l WHERE l.article_id=a.id) as like_count,
                   (SELECT COUNT(*) FROM bookmarks b WHERE b.article_id=a.id) as bookmark_count
            FROM articles a $whereSQL
            ORDER BY a.featured DESC, a.created_at DESC
            LIMIT ? OFFSET ?";
    $allParams = array_merge($params, [$limit, $offset]);
    $allTypes  = $types . 'ii';
    $stmt      = $conn->prepare($sql);
    $stmt->bind_param($allTypes, ...$allParams);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    foreach ($rows as &$r) {
        $r['date']           = date('M j, Y', strtotime($r['created_at']));
        $r['featured']       = (bool)$r['featured'];
        $r['like_count']     = (int)$r['like_count'];
        $r['bookmark_count'] = (int)$r['bookmark_count'];
    }

    sendJSON(['success' => true, 'articles' => $rows, 'total' => (int)$total]);
}

/* ── GET single article ────────────────────────── */
if ($action === 'get') {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) sendJSON(['success' => false, 'message' => 'Invalid ID'], 400);

    $stmt = $conn->prepare(
        "SELECT a.*,
                (SELECT COUNT(*) FROM likes    l WHERE l.article_id=a.id) as like_count,
                (SELECT COUNT(*) FROM bookmarks b WHERE b.article_id=a.id) as bookmark_count
         FROM articles a WHERE a.id=? AND a.published=1"
    );
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if (!$row) sendJSON(['success' => false, 'message' => 'Article not found'], 404);

    $row['date']           = date('M j, Y', strtotime($row['created_at']));
    $row['featured']       = (bool)$row['featured'];
    $row['like_count']     = (int)$row['like_count'];
    $row['bookmark_count'] = (int)$row['bookmark_count'];

    $row['user_liked'] = $row['user_bookmarked'] = false;
    if (isset($_SESSION['user_id'])) {
        $uid = (int)$_SESSION['user_id'];
        $row['user_liked']      = $conn->query("SELECT id FROM likes     WHERE user_id=$uid AND article_id=$id")->num_rows > 0;
        $row['user_bookmarked'] = $conn->query("SELECT id FROM bookmarks WHERE user_id=$uid AND article_id=$id")->num_rows > 0;
    }

    sendJSON(['success' => true, 'article' => $row]);
}

/* ── CREATE article (admin only) ───────────────── */
if ($action === 'create' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data = json_decode(file_get_contents('php://input'), true);

    $title    = trim($data['title']           ?? '');
    $excerpt  = trim($data['excerpt']         ?? '');
    $content  = trim($data['content']         ?? '');
    $author   = trim($data['author']          ?? '');
    $initials = trim($data['author_initials'] ?? '');
    $role     = trim($data['author_role']     ?? '');
    $color    = trim($data['author_color']    ?? 'linear-gradient(135deg,#90CAF9,#1565c0)');
    $readTime = trim($data['read_time']       ?? '5 min');
    $category = trim($data['category']        ?? '');
    $catLabel = trim($data['category_label']  ?? $category);
    $featured = (int)(!empty($data['featured']));
    $published= (int)(!empty($data['published']));

    if (!$title || !$excerpt || !$content || !$author || !$category)
        sendJSON(['success' => false, 'message' => 'Required fields missing'], 400);

    $stmt = $conn->prepare(
        "INSERT INTO articles
         (category, category_label, title, excerpt, content, author,
          author_initials, author_role, author_color, read_time, featured, published, created_at)
         VALUES (?,?,?,?,?,?,?,?,?,?,?,?, NOW())"
    );
    $stmt->bind_param('ssssssssssii',
        $category, $catLabel, $title, $excerpt, $content, $author,
        $initials, $role, $color, $readTime, $featured, $published
    );

    if ($stmt->execute()) {
        $newId = $conn->insert_id;
        $stmt->close();
        sendJSON(['success' => true, 'message' => 'Article created!', 'id' => $newId]);
    } else {
        sendJSON(['success' => false, 'message' => 'DB error: ' . $conn->error], 500);
    }
}

/* ── UPDATE article (admin only) ───────────────── */
if ($action === 'update' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);
    if (!$id) sendJSON(['success' => false, 'message' => 'ID required'], 400);

    $title    = trim($data['title']           ?? '');
    $excerpt  = trim($data['excerpt']         ?? '');
    $content  = trim($data['content']         ?? '');
    $author   = trim($data['author']          ?? '');
    $initials = trim($data['author_initials'] ?? '');
    $role     = trim($data['author_role']     ?? '');
    $color    = trim($data['author_color']    ?? 'linear-gradient(135deg,#90CAF9,#1565c0)');
    $readTime = trim($data['read_time']       ?? '5 min');
    $category = trim($data['category']        ?? '');
    $catLabel = trim($data['category_label']  ?? $category);
    $featured = (int)(!empty($data['featured']));
    $published= isset($data['published']) ? (int)$data['published'] : 1;

    if (!$title || !$excerpt || !$content || !$author || !$category)
        sendJSON(['success' => false, 'message' => 'Required fields missing'], 400);

    $stmt = $conn->prepare(
        "UPDATE articles SET
            category=?, category_label=?, title=?, excerpt=?, content=?,
            author=?, author_initials=?, author_role=?, author_color=?,
            read_time=?, featured=?, published=?
         WHERE id=?"
    );
    $stmt->bind_param('ssssssssssiiii',
        $category, $catLabel, $title, $excerpt, $content, $author,
        $initials, $role, $color, $readTime, $featured, $published, $id
    );

    if ($stmt->execute()) {
        $stmt->close();
        sendJSON(['success' => true, 'message' => 'Article updated!']);
    } else {
        sendJSON(['success' => false, 'message' => 'DB error: ' . $conn->error], 500);
    }
}

/* ── DELETE article (admin only) ───────────────── */
if ($action === 'delete' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    requireLogin();
    if ($_SESSION['user_role'] !== 'admin')
        sendJSON(['success' => false, 'message' => 'Admin only'], 403);

    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);
    if (!$id) sendJSON(['success' => false, 'message' => 'ID required'], 400);

    // Delete related likes and bookmarks first
    $conn->query("DELETE FROM likes     WHERE article_id=$id");
    $conn->query("DELETE FROM bookmarks WHERE article_id=$id");

    $stmt = $conn->prepare("DELETE FROM articles WHERE id=?");
    $stmt->bind_param('i', $id);
    if ($stmt->execute() && $stmt->affected_rows > 0) {
        $stmt->close();
        sendJSON(['success' => true, 'message' => 'Article deleted']);
    } else {
        sendJSON(['success' => false, 'message' => 'Article not found or already deleted'], 404);
    }
}

/* ── IMPORT from JS array (one-time, admin only) ── */
if ($action === 'import' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $secret = $_GET['secret'] ?? '';
    if ($secret !== 'gocosys_import_2026')
        sendJSON(['success' => false, 'message' => 'Unauthorized'], 401);

    $data     = json_decode(file_get_contents('php://input'), true);
    $articles = $data['articles'] ?? [];
    if (empty($articles)) sendJSON(['success' => false, 'message' => 'No articles provided']);

    $imported = 0;
    foreach ($articles as $a) {
        $cat   = $conn->real_escape_string($a['category']       ?? '');
        $clab  = $conn->real_escape_string($a['categoryLabel']  ?? '');
        $title = $conn->real_escape_string($a['title']          ?? '');
        $exc   = $conn->real_escape_string($a['excerpt']        ?? '');
        $cont  = $conn->real_escape_string($a['content']        ?? '');
        $auth  = $conn->real_escape_string($a['author']         ?? '');
        $ainit = $conn->real_escape_string($a['authorInitials'] ?? '');
        $arole = $conn->real_escape_string($a['authorRole']     ?? '');
        $acol  = $conn->real_escape_string($a['authorColor']    ?? '');
        $rtime = $conn->real_escape_string($a['readTime']       ?? '5 min');
        $feat  = (int)(!empty($a['featured']));
        $dated = date('Y-m-d H:i:s', @strtotime($a['date'] ?? '') ?: time());

        $conn->query("INSERT INTO articles
            (category,category_label,title,excerpt,content,author,author_initials,author_role,author_color,read_time,featured,published,created_at)
            VALUES ('$cat','$clab','$title','$exc','$cont','$auth','$ainit','$arole','$acol','$rtime',$feat,1,'$dated')");
        if ($conn->affected_rows > 0) $imported++;
    }

    sendJSON(['success' => true, 'imported' => $imported, 'message' => "$imported articles imported!"]);
}

sendJSON(['success' => false, 'message' => 'Unknown action'], 400);
